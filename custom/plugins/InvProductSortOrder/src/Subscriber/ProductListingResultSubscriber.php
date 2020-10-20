<?php declare(strict_types=1);

namespace InvProductSortOrder\Subscriber;

use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\Events\ProductSearchResultEvent;
use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingFeaturesSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ProductListingResultSubscriber implements EventSubscriberInterface
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(
        SystemConfigService $systemConfigService
    ) {
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductListingCriteriaEvent::class  => 'handleRequest',
            ProductListingResultEvent::class => 'handleResult',
            ProductSearchCriteriaEvent::class  => 'handleRequest',
            ProductSearchResultEvent::class => 'handleResult'
        ];
    }

    /**
     * @param ProductListingResultEvent $event
     */
    public function handleResult(ProductListingResultEvent $event): void
    {
        $status = $this->systemConfigService->get('InvProductSortOrder.config.status') ?? 0;

        if ($status == 1) {
            $productSortOrder = $this->systemConfigService->get('InvProductSortOrder.config.productSortOrder') ?? ProductListingFeaturesSubscriber::DEFAULT_SORT;
            $request = $event->getRequest();

            if (!$request->get('order')) {
                $event->getResult()->setSorting($productSortOrder);
            }
        }
    }

    /**
     * @param ProductListingCriteriaEvent $event
     */
    public function handleRequest(ProductListingCriteriaEvent $event): void
    {
        $status = $this->systemConfigService->get('InvProductSortOrder.config.status') ?? 0;

        if ($status == 1) {
            $productSortOrder = $this->systemConfigService->get('InvProductSortOrder.config.productSortOrder') ?? ProductListingFeaturesSubscriber::DEFAULT_SORT;
            $productSortOrderParts = explode('-', $productSortOrder);

            if (!empty($productSortOrderParts) && count($productSortOrderParts) == 2) {
                $request = $event->getRequest();
                $criteria = $event->getCriteria();

                if (!$request->get('order')) {
                    $criteria->resetSorting();
                    $criteria->addSorting(
                        new FieldSorting($productSortOrderParts[0], $productSortOrderParts[1])
                    );
                }
            }
        }
    }
}
