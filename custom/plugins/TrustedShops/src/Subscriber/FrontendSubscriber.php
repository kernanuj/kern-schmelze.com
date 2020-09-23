<?php declare(strict_types=1);

namespace TrustedShops\Subscriber;

use Petstore30\Order;
use Shopware\Core\Checkout\Cart\Event\LineItemAddedEvent;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\DeliveryTime\DeliveryTimeEntity;
use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPageLoadedEvent;
use Shopware\Storefront\Page\Product\ProductLoaderCriteriaEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FrontendSubscriber implements EventSubscriberInterface
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;
    private $productRepository;
    private $trustedshopProductRatingRepository;

    /**
     * @var string
     */
    private $shopReviewsApiUrl = 'https://api.trustedshops.com/rest/public/v2/shops/{{tsId}}/reviews';

    /**
     * @var int
     */
    private $shopReviewsCheckInterval = 3600;

    public function __construct(SystemConfigService $systemConfigService, EntityRepositoryInterface $productRepository, EntityRepositoryInterface $trustedshopProductRatingRepository)
    {
        $this->systemConfigService = $systemConfigService;
        $this->productRepository = $productRepository;
        $this->trustedshopProductRatingRepository = $trustedshopProductRatingRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorefrontRenderEvent::class => 'onStoreFrontRender',
            ProductLoaderCriteriaEvent::class => 'onProductLoaderCriteria',
            ProductListingCriteriaEvent::class => 'onProductListingCriteria',
            LineItemAddedEvent::class => 'onLineItemAdded',
            CheckoutFinishPageLoadedEvent::class => 'onCheckoutFinishPageLoaded',
        ];
    }

    public function onStoreFrontRender(StorefrontRenderEvent $event): void
    {
        $isAjax = $event->getRequest()->isXmlHttpRequest();

        if (!$isAjax) {
            $this->initTrustedShopsShopReviews();
        }

    }

    public function onProductLoaderCriteria(ProductLoaderCriteriaEvent $event): void
    {
        $event->getCriteria()->addAssociation('extensions.trustedshopsRatings');
    }

    public function onProductListingCriteria(ProductListingCriteriaEvent $event): void
    {
        $event->getCriteria()->addAssociation('extensions.trustedshopsRatings');
    }

    public function onLineItemAdded(LineItemAddedEvent $event): void
    {
        $lineItem = $event->getLineItem();

        if( $lineItem->getType() === 'product' ) {
            $context = Context::createDefaultContext();

            /** @var ProductEntity $product */
            $product = $this->productRepository->search((new Criteria([$lineItem->getReferencedId()])),$context)->first();

            if( ( $parentId = $product->getParentId()) ) {
                /** @var ProductEntity $parent */
                $parent = $this->productRepository->search((new Criteria([$parentId]))->addAssociation('cover'),$context)->first();

                $lineItem->setPayloadValue('parent',[
                    'productId' => $parent->getId(),
                    'productNumber' => $parent->getProductNumber(),
                    'label' => $parent->getTranslation('name'),
                    'cover' => ( $parent->getCover() ? $parent->getCover()->getMedia() : null )
                ]);
            }
        }

    }


    public function onCheckoutFinishPageLoaded(CheckoutFinishPageLoadedEvent $event): void {
        $page = $event->getPage();
        $page->assign(['tsDeliveryTime' => $this->getTrustedShopsDeliveryTime( $page->getOrder() ) ] );
    }


    protected function getTrustedShopsDeliveryTime( OrderEntity $order )
    {
        $customAvailableDeliverTime = ( $this->systemConfigService->get( 'TrustedShops.config.tsAvailableProductDeliveryTime' ) === 'custom' );
        $customUnavailableDeliverTime = ( $this->systemConfigService->get( 'TrustedShops.config.tsNotAvailableProductDeliveryTime' ) === 'custom' );

        $availableDeliveryTimeDays = $this->systemConfigService->get( 'TrustedShops.config.tsAvailableProductDeliveryTimeDays' );
        $unavailableDeliveryTimeDays = $this->systemConfigService->get( 'TrustedShops.config.tsNotAvailableProductDeliveryTimeDays' );

        if( $this->orderContainsUnavailableProducts( $order ) ) {
            if( $customUnavailableDeliverTime && $unavailableDeliveryTimeDays > 0 ) {
                $deliveryTimeDays = $unavailableDeliveryTimeDays;
            } else {
                return $order->getDeliveries()->first()->getShippingDateLatest();
            }
        } else {
            if( $customAvailableDeliverTime && $availableDeliveryTimeDays > 0 ) {
                $deliveryTimeDays = $availableDeliveryTimeDays;
            } else {
                return $order->getDeliveries()->first()->getShippingDateLatest();
            }
        }

        switch (date('N')) {
            case 5: //freitag
                $deliveryTimeDays += 2;
                break;
            case 6: //samstag
                $deliveryTimeDays += 1;
                break;
        }

        if( date( 'N', strtotime( '+' . $deliveryTimeDays . 'days' ) ) === 7 ) {
            $deliveryTimeDays++;
        }

        return (new \DateTime())->add(new \DateInterval('P' . $deliveryTimeDays . 'D'));
    }


    protected function orderContainsUnavailableProducts( OrderEntity $order ): bool
    {
        $context = Context::createDefaultContext();

        /** @var OrderLineItemEntity $lineItem */
        foreach( $order->getLineItems() as $lineItem ) {
            if( $lineItem->getType() === 'product' ) {
                /** @var ProductEntity $product */
                $product = $this->productRepository->search((new Criteria([$lineItem->getProductId()])),$context)->first();
                if( !$product->getAvailable() ) {
                    return true;
                }
            }

        }

        return false;
    }


    protected function initTrustedShopsShopReviews(): void
    {
        $domain = 'TrustedShops.config.';
        $tsId = $this->systemConfigService->get( $domain . 'tsId' );

        if( $tsId ) {

            $lastCheck = $this->systemConfigService->get( $domain . 'tsShopRatingLastCheck' );

            if( empty( $lastCheck ) || ( $lastCheck+$this->shopReviewsCheckInterval ) < time() ) {

                $apiUrl = str_replace( '{{tsId}}', $tsId, $this->shopReviewsApiUrl );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'Accept: application/json' ] );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $apiUrl);
                $result = curl_exec($ch);
                curl_close($ch);

                if( $result ) {
                    $shopReviews = json_decode($result);

                    if ($shopReviews && $shopReviews->response && $shopReviews->response->data->shop->reviews) {

                        $bestRating = 0;
                        $_ratings = [];

                        foreach ($shopReviews->response->data->shop->reviews as $review) {
                            $_rating = (float)$review->mark;
                            $_ratings[] = $_rating;

                            if ($_rating > $bestRating) {
                                $bestRating = $_rating;
                            }
                        }

                        $ratingCount = count($shopReviews->response->data->shop->reviews);
                        $avgRating = array_sum($_ratings) / $ratingCount;

                        $this->systemConfigService->set($domain . 'tsShopAvgRating', $avgRating);
                        $this->systemConfigService->set($domain . 'tsShopBestRating', $bestRating);
                        $this->systemConfigService->set($domain . 'tsShopRatingCount', $ratingCount);
                        $this->systemConfigService->set($domain . 'tsShopRatingLastCheck', time());
                    }
                }
            }
        }
    }

}