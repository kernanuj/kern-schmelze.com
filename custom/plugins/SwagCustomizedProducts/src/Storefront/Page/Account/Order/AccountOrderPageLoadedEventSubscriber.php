<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Page\Account\Order;

use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Storefront\Page\Account\Order\AccountOrderPageLoadedEvent;
use Shopware\Storefront\Page\Account\Overview\AccountOverviewPageLoadedEvent;
use Shopware\Storefront\Page\PageLoadedEvent;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccountOrderPageLoadedEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AccountOrderPageLoadedEvent::class => 'nestOrderLineItems',
            AccountOverviewPageLoadedEvent::class => 'nestOrderLineItems',
        ];
    }

    /**
     * @param AccountOrderPageLoadedEvent|AccountOverviewPageLoadedEvent $event
     */
    public function nestOrderLineItems(PageLoadedEvent $event): void
    {
        if ($event instanceof AccountOverviewPageLoadedEvent) {
            $orderCollection = new OrderCollection([$event->getPage()->getNewestOrder()]);
        } else {
            $orderCollection = $event->getPage()->getOrders()->getEntities();
        }

        foreach ($orderCollection as $orderEntity) {
            $orderLineItemCollection = $orderEntity->getLineItems();
            if ($orderLineItemCollection === null) {
                continue;
            }

            $templates = $orderLineItemCollection->filterByType(
                CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
            );
            if ($templates->count() <= 0) {
                continue;
            }

            $nestedLineItems = $orderEntity->getNestedLineItems();
            if ($nestedLineItems === null) {
                continue;
            }

            $orderEntity->setLineItems($nestedLineItems);
        }
    }
}
