<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Extension\Hydrator\InstantShopping;

use KlarnaPayment\Components\Client\Hydrator\Struct\LineItem\LineItemStructHydratorInterface;
use KlarnaPayment\Components\Client\Struct\LineItem;
use KlarnaPayment\Components\ConfigReader\ConfigReaderInterface;
use KlarnaPayment\Components\Helper\SeoUrlHelper\SeoUrlHelperInterface;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPage;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPage;
use Shopware\Storefront\Page\Page;
use Shopware\Storefront\Page\Product\ProductPage;

class DataExtensionHydrator implements DataExtensionHydratorInterface
{
    /** @var SeoUrlHelperInterface */
    private $seoUrlHelper;

    /** @var LineItemStructHydratorInterface */
    private $lineItemStructHydrator;

    /** @var ConfigReaderInterface */
    private $configReader;

    public function __construct(
        SeoUrlHelperInterface $seoUrlHelper,
        LineItemStructHydratorInterface $lineItemStructHydrator,
        ConfigReaderInterface $configReader
    ) {
        $this->seoUrlHelper           = $seoUrlHelper;
        $this->lineItemStructHydrator = $lineItemStructHydrator;
        $this->configReader           = $configReader;
    }

    public function hydrateMerchantUrls(string $salesChannelDomainId, SalesChannelContext $salesChannelContext): array
    {
        $pluginConfig = $this->configReader->read($salesChannelContext->getSalesChannel()->getId());

        $result = [
            'place_order' => $this->seoUrlHelper->getSeoUrlFromDomainId(
                [],
                $salesChannelDomainId,
                $salesChannelContext,
                'frontend.klarna.instantShopping.placeOrder'
            ),
        ];

        if ($pluginConfig->get('termsCategory')) {
            $termsUrl = $this->seoUrlHelper->getSeoUrlFromDomainId(
                ['navigationId' => (string) $pluginConfig->get('termsCategory')],
                $salesChannelDomainId,
                $salesChannelContext
            );

            if (!empty($termsUrl)) {
                $result['terms'] = $termsUrl;
            }
        }

        return $result;
    }

    public function hydrateActionUrls(string $salesChannelDomainId, SalesChannelContext $salesChannelContext): array
    {
        return [
            'initiateSessionUrl' => $this->seoUrlHelper->getSeoUrlFromDomainId(
                [],
                $salesChannelDomainId,
                $salesChannelContext,
                'frontend.klarna.instantShopping.updateIdentification'
            ),
            'updateDataUrl' => $this->seoUrlHelper->getSeoUrlFromDomainId(
                [],
                $salesChannelDomainId,
                $salesChannelContext,
                'frontend.klarna.instantShopping.updateData'
            ),
            'updateIdentificationUrl' => $this->seoUrlHelper->getSeoUrlFromDomainId(
                [],
                $salesChannelDomainId,
                $salesChannelContext,
                'frontend.klarna.instantShopping.updateIdentification'
            ),
            'updateShippingUrl' => $this->seoUrlHelper->getSeoUrlFromDomainId(
                [],
                $salesChannelDomainId,
                $salesChannelContext,
                'frontend.klarna.instantShopping.updateShipping'
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function hydrateOrderLines(
        Page $page,
        string $salesChannelDomainEntity,
        SalesChannelContext $salesChannelContext
    ): array {
        if ($page instanceof ProductPage) {
            /**
             * Initialize a fake line item because the actual cart is being created once the button was clicked but the load call requires order_lines as required parameter.
             * This reduces the number of generated carts and does not require an AJAX call on every product page (which would be needed to handle the HTTP cache)
             *
             * @see https://x.klarnacdn.net/instantshopping/lib/v1/index.html#instantshoppingloadoptions
             */
            $orderLines = [$this->createLineItemByProduct($page->getProduct())];
        } elseif ($page instanceof CheckoutCartPage || $page instanceof OffcanvasCartPage) {
            $orderLines = $this->lineItemStructHydrator->hydrate(
                $page->getCart()->getLineItems(),
                $salesChannelContext->getCurrency(),
                $salesChannelContext->getContext()
            );
        } else {
            $orderLines = [];
        }

        return $this->addProductUrls($orderLines, $salesChannelContext, $salesChannelDomainEntity);
    }

    private function addProductUrls(array $orderLines, SalesChannelContext $salesChannelContext, string $salesChannelDomainEntity): array
    {
        foreach ($orderLines as $orderLine) {
            if (!($orderLine instanceof LineItem)
                || $orderLine->getType() !== LineItem::TYPE_PHYSICAL
                || empty($orderLine->getProductId())) {
                continue;
            }

            $orderLine->assign([
                'productUrl' => $this->seoUrlHelper->getSeoUrlFromDomainId(
                    ['productId' => $orderLine->getProductId()],
                    $salesChannelDomainEntity,
                    $salesChannelContext,
                    'frontend.detail.page'
                ),
            ]);
        }

        return $orderLines;
    }

    private function createLineItemByProduct(SalesChannelProductEntity $product): LineItem
    {
        return (new LineItem())->assign([
            'productId'      => $product->getId(),
            'type'           => LineItem::TYPE_PHYSICAL,
            'reference'      => $product->getProductNumber(),
            'name'           => $product->getTranslated()['name'] ?: $product->getName(),
            'imageUrl'       => $this->getMediaUrlFromCover($product->getCover()),
            'quantity'       => 1,
            'unitPrice'      => 1,
            'taxRate'        => 0,
            'totalAmount'    => 1,
            'totalTaxAmount' => 0,
        ]);
    }

    private function getMediaUrlFromCover(?ProductMediaEntity $media): ?string
    {
        if ($media === null || $media->getMedia() === null) {
            return null;
        }

        return $media->getMedia()->getUrl();
    }
}
