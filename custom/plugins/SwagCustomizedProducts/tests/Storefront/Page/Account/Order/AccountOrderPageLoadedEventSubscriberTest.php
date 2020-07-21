<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Storefront\Page\Account\Order;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Storefront\Framework\Page\StorefrontSearchResult;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPage;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPageLoadedEvent;
use Shopware\Storefront\Page\Account\Order\AccountOrderPage;
use Shopware\Storefront\Page\Account\Order\AccountOrderPageLoadedEvent;
use Shopware\Storefront\Page\Account\Overview\AccountOverviewPage;
use Shopware\Storefront\Page\Account\Overview\AccountOverviewPageLoadedEvent;
use Shopware\Storefront\Page\PageLoadedEvent;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Storefront\Page\Account\Order\AccountOrderPageLoadedEventSubscriber;
use Symfony\Component\HttpFoundation\Request;

class AccountOrderPageLoadedEventSubscriberTest extends TestCase
{
    use KernelTestBehaviour;

    public function testGetSubscribedEvents(): void
    {
        static::assertSame(
            [
                AccountOrderPageLoadedEvent::class => 'nestOrderLineItems',
                AccountOverviewPageLoadedEvent::class => 'nestOrderLineItems',
                AccountEditOrderPageLoadedEvent::class => 'nestOrderLineItems',
            ],
            AccountOrderPageLoadedEventSubscriber::getSubscribedEvents()
        );
    }

    public function testThatOrderWithoutCustomizedProductDoesNotReturnNestedLineItems(): void
    {
        $subscriber = new AccountOrderPageLoadedEventSubscriber();
        $event = $this->getEvent();
        static::assertInstanceOf(AccountOrderPageLoadedEvent::class, $event);

        $subscriber->nestOrderLineItems($event);
        /** @var OrderEntity|null $order */
        $order = $event->getPage()->getOrders()->first();
        static::assertNotNull($order);
        $orderLineItemCollection = $order->getLineItems();
        static::assertNotNull($orderLineItemCollection);
        static::assertCount(2, $orderLineItemCollection);
    }

    public function testAccountOverviewLastOrderWithoutOrders(): void
    {
        $subscriber = new AccountOrderPageLoadedEventSubscriber();
        $reflectionMethod = (new \ReflectionClass(AccountOrderPageLoadedEventSubscriber::class))
            ->getMethod('getOrderCollection');
        $reflectionMethod->setAccessible(true);

        $event = $this->getEvent();
        static::assertInstanceOf(AccountOrderPageLoadedEvent::class, $event);
        $event->getPage()->setOrders(
            new StorefrontSearchResult(
                0,
                new OrderCollection(),
                null,
                new Criteria(),
                Context::createDefaultContext()
            )
        );

        /** @var EntityCollection $orderCollection */
        $orderCollection = $reflectionMethod->invoke($subscriber, $event);

        static::assertCount(0, $orderCollection->getElements());
    }

    public function testAccountOverviewLastOrderWithOrders(): void
    {
        $subscriber = new AccountOrderPageLoadedEventSubscriber();
        $reflectionMethod = (new \ReflectionClass(AccountOrderPageLoadedEventSubscriber::class))
            ->getMethod('getOrderCollection');
        $reflectionMethod->setAccessible(true);

        $event = $this->getEvent(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );
        static::assertInstanceOf(AccountOrderPageLoadedEvent::class, $event);

        /** @var EntityCollection $orderCollection */
        $orderCollection = $reflectionMethod->invoke($subscriber, $event);

        static::assertCount(1, $orderCollection->getElements());
    }

    public function testThatOrderWithCustomizedProductReturnsNestedLineItems(): void
    {
        $subscriber = new AccountOrderPageLoadedEventSubscriber();
        $event = $this->getEvent(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );
        static::assertInstanceOf(AccountOrderPageLoadedEvent::class, $event);

        $subscriber->nestOrderLineItems($event);
        /** @var OrderEntity|null $order */
        $order = $event->getPage()->getOrders()->first();
        static::assertNotNull($order);
        $orderLineItemCollection = $order->getLineItems();
        static::assertNotNull($orderLineItemCollection);
        static::assertCount(1, $orderLineItemCollection);
    }

    public function testThatEditOrderWithCustomizedProductReturnsNestedLineItems(): void
    {
        $subscriber = new AccountOrderPageLoadedEventSubscriber();
        $event = $this->getEvent(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            AccountEditOrderPageLoadedEvent::class
        );
        static::assertInstanceOf(AccountEditOrderPageLoadedEvent::class, $event);

        $subscriber->nestOrderLineItems($event);
        /** @var OrderEntity|null $order */
        $order = $event->getPage()->getOrder();
        static::assertNotNull($order);
        $orderLineItemCollection = $order->getLineItems();
        static::assertNotNull($orderLineItemCollection);
        static::assertCount(1, $orderLineItemCollection);
    }

    public function testThatOrderOverviewWithNewestOrderHasNestedLineItems(): void
    {
        $subscriber = new AccountOrderPageLoadedEventSubscriber();
        $event = $this->getEvent(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            AccountOverviewPageLoadedEvent::class
        );
        static::assertInstanceOf(AccountOverviewPageLoadedEvent::class, $event);

        $subscriber->nestOrderLineItems($event);
        /** @var OrderEntity|null $order */
        $order = $event->getPage()->getNewestOrder();
        static::assertNotNull($order);
        $orderLineItemCollection = $order->getLineItems();
        static::assertNotNull($orderLineItemCollection);
        static::assertCount(1, $orderLineItemCollection);
    }

    /**
     * @return AccountOrderPageLoadedEvent|AccountOverviewPageLoadedEvent|AccountEditOrderPageLoadedEvent
     */
    private function getEvent(
        string $parentType = LineItem::PRODUCT_LINE_ITEM_TYPE,
        string $childType = LineItem::CUSTOM_LINE_ITEM_TYPE,
        string $event = AccountOrderPageLoadedEvent::class
    ): PageLoadedEvent {
        /** @var SalesChannelContextFactory $salesChannelContextFactory */
        $salesChannelContextFactory = $this->getContainer()->get(SalesChannelContextFactory::class);
        $salesChannelContext = $salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);
        $orderEntity = new OrderEntity();
        $orderEntity->setId(Uuid::randomHex());
        $parentId = Uuid::randomHex();
        $parentLineItem = new OrderLineItemEntity();
        $parentLineItem->setId($parentId);
        $parentLineItem->setType($parentType);
        $childLineItem = new OrderLineItemEntity();
        $childId = Uuid::randomHex();
        $childLineItem->setId($childId);
        $childLineItem->setParentId($parentId);
        $childLineItem->setType($childType);
        $orderEntity->setLineItems(new OrderLineItemCollection([$parentLineItem, $childLineItem]));

        if ($event === AccountOverviewPageLoadedEvent::class) {
            $page = new AccountOverviewPage();
            $page->setNewestOrder($orderEntity);

            return new $event($page, $salesChannelContext, new Request());
        }

        if ($event === AccountEditOrderPageLoadedEvent::class) {
            $page = new AccountEditOrderPage();
            $page->setOrder($orderEntity);

            return new $event($page, $salesChannelContext, new Request());
        }

        $page = new AccountOrderPage();
        $page->setOrders(
            new StorefrontSearchResult(
                1,
                new OrderCollection([$orderEntity]),
                new AggregationResultCollection(),
                new Criteria(),
                $salesChannelContext->getContext()
            )
        );

        return new $event($page, $salesChannelContext, new Request());
    }
}
