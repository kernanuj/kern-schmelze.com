<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout\Promotion\Cart\Discount\ScopePackager;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemQuantityCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use Shopware\Core\Checkout\Cart\Rule\LineItemRule;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountLineItem;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackage;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackager;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Core\Checkout\Promotion\Cart\Discount\ScopePackager\CartScopeDiscountPackagerDecorator;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class CartScopeDiscountPackagerDecoratorTest extends TestCase
{
    use ServicesTrait;

    /**
     * @var SalesChannelContextFactory
     */
    private $salesChannelContextFactory;

    protected function setUp(): void
    {
        $this->salesChannelContextFactory = $this->getContainer()->get(SalesChannelContextFactory::class);
    }

    public function testGetDecorated(): void
    {
        /**
         * @var DiscountPackager|MockObject $discountPackagerMock
         */
        $discountPackagerMock = $this->getMockBuilder(DiscountPackager::class)->getMock();
        $cartScopeDiscountPackagerDecorator = new CartScopeDiscountPackagerDecorator($discountPackagerMock);

        static::assertSame($discountPackagerMock, $cartScopeDiscountPackagerDecorator->getDecorated());
    }

    public function testGetResultContext(): void
    {
        /**
         * @var DiscountPackager|MockObject $discountPackagerMock
         */
        $discountPackagerMock = $this->getMockBuilder(DiscountPackager::class)->getMock();
        $discountPackagerMock->method('getResultContext')
            ->willReturn('test');
        $cartScopeDiscountPackagerDecorator = new CartScopeDiscountPackagerDecorator($discountPackagerMock);

        static::assertSame('test', $cartScopeDiscountPackagerDecorator->getResultContext());
    }

    public function testGetMatchingItemsWithoutCustomProductReturnsOriginalItems(): void
    {
        $discountPackage = $this->getDiscountPackageCollection();
        /**
         * @var DiscountPackager|MockObject $discountPackagerMock
         */
        $discountPackagerMock = $this->getMockBuilder(DiscountPackager::class)->getMock();
        $discountPackagerMock->method('getMatchingItems')
            ->willReturn($discountPackage);
        $decorated = new CartScopeDiscountPackagerDecorator($discountPackagerMock);
        $discountItem = $this->getDiscountLineItem();
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);
        $cart = new Cart(Uuid::randomHex(), Uuid::randomHex());

        $result = $decorated->getMatchingItems($discountItem, $cart, $salesChannelContext);
        static::assertSame($discountPackage, $result);
    }

    public function testGetMatchingItems(): void
    {
        $discountPackage = $this->getDiscountPackageCollection();
        /**
         * @var DiscountPackager|MockObject $discountPackagerMock
         */
        $discountPackagerMock = $this->getMockBuilder(DiscountPackager::class)->getMock();
        $discountPackagerMock->method('getMatchingItems')
            ->willReturn($discountPackage);
        $decorated = new CartScopeDiscountPackagerDecorator($discountPackagerMock);
        $discountItem = $this->getDiscountLineItem();
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);
        $customizedProductLineItemId = Uuid::randomHex();
        $customizedProductLineItem = new LineItem(
            $customizedProductLineItemId,
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $customizedProductLineItem->addChild(
            new LineItem(
                Uuid::randomHex(),
                LineItem::PRODUCT_LINE_ITEM_TYPE
            )
        );
        $cart = new Cart(Uuid::randomHex(), Uuid::randomHex());
        $cart->add($customizedProductLineItem);

        $result = $decorated->getMatchingItems($discountItem, $cart, $salesChannelContext);
        $customizedProductDiscountPackage = $result->last();
        static::assertNotNull($customizedProductDiscountPackage);
        $customizedProductLineItemQuantity = $customizedProductDiscountPackage->getMetaData()->first();
        static::assertNotNull($customizedProductLineItemQuantity);
        static::assertSame($customizedProductLineItemId, $customizedProductLineItemQuantity->getLineItemId());
    }

    public function testGetMatchingItemsWithoutProductLineItemGetsRemoved(): void
    {
        $discountPackage = $this->getDiscountPackageCollection();
        /**
         * @var DiscountPackager|MockObject $discountPackagerMock
         */
        $discountPackagerMock = $this->getMockBuilder(DiscountPackager::class)->getMock();
        $discountPackagerMock->method('getMatchingItems')
            ->willReturn($discountPackage);
        $decorated = new CartScopeDiscountPackagerDecorator($discountPackagerMock);
        $discountItem = $this->getDiscountLineItem();
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);
        $customizedProductLineItemId = Uuid::randomHex();
        $customizedProductLineItem = new LineItem(
            $customizedProductLineItemId,
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $cart = new Cart(Uuid::randomHex(), Uuid::randomHex());
        $cart->add($customizedProductLineItem);

        $result = $decorated->getMatchingItems($discountItem, $cart, $salesChannelContext);
        static::assertSame($discountPackage, $result);
    }

    public function testGetMatchingItemsWithMatchingRule(): void
    {
        $discountPackage = $this->getDiscountPackageCollection();
        /**
         * @var DiscountPackager|MockObject $discountPackagerMock
         */
        $discountPackagerMock = $this->getMockBuilder(DiscountPackager::class)->getMock();
        $discountPackagerMock->method('getMatchingItems')
            ->willReturn($discountPackage);
        $decorated = new CartScopeDiscountPackagerDecorator($discountPackagerMock);

        /** @var MockObject|LineItemRule $filter */
        $filter = $this->getMockBuilder(LineItemRule::class)->getMock();
        $filter->method('match')
            ->withAnyParameters()
            ->willReturn(true);

        $discountItem = $this->getDiscountLineItem($filter);
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);
        $customizedProductLineItemId = Uuid::randomHex();
        $customizedProductLineItem = new LineItem(
            $customizedProductLineItemId,
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $customizedProductLineItem->addChild(
            new LineItem(
                Uuid::randomHex(),
                LineItem::PRODUCT_LINE_ITEM_TYPE
            )
        );
        $cart = new Cart(Uuid::randomHex(), Uuid::randomHex());
        $cart->add($customizedProductLineItem);

        $result = $decorated->getMatchingItems($discountItem, $cart, $salesChannelContext);
        $customizedProductDiscountPackage = $result->last();
        static::assertNotNull($customizedProductDiscountPackage);
        $customizedProductLineItemQuantity = $customizedProductDiscountPackage->getMetaData()->first();
        static::assertNotNull($customizedProductLineItemQuantity);
        static::assertSame($customizedProductLineItemId, $customizedProductLineItemQuantity->getLineItemId());
    }

    public function testGetMatchingItemsWithoutMatchingRuleGetsRemoved(): void
    {
        $discountPackage = $this->getDiscountPackageCollection();
        /**
         * @var DiscountPackager|MockObject $discountPackagerMock
         */
        $discountPackagerMock = $this->getMockBuilder(DiscountPackager::class)->getMock();
        $discountPackagerMock->method('getMatchingItems')
            ->willReturn($discountPackage);
        $decorated = new CartScopeDiscountPackagerDecorator($discountPackagerMock);
        $filter = $this->getMockBuilder(LineItemRule::class)->getMock();
        $filter->method('match')
            ->withAnyParameters()
            ->willReturn(false);
        $discountItem = $this->getDiscountLineItem($filter);
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);
        $customizedProductLineItemId = Uuid::randomHex();
        $customizedProductLineItem = new LineItem(
            $customizedProductLineItemId,
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $customizedProductLineItem->addChild(
            new LineItem(
                Uuid::randomHex(),
                LineItem::PRODUCT_LINE_ITEM_TYPE
            )
        );
        $cart = new Cart(Uuid::randomHex(), Uuid::randomHex());
        $cart->add($customizedProductLineItem);

        $result = $decorated->getMatchingItems($discountItem, $cart, $salesChannelContext);
        static::assertSame($discountPackage, $result);
    }

    private function getDiscountLineItem(?Rule $filter = null): DiscountLineItem
    {
        $priceDefinition = new AbsolutePriceDefinition(-10, 2);
        if ($filter !== null) {
            $priceDefinition = new AbsolutePriceDefinition(-10, 2, $filter);
        }

        return new DiscountLineItem('10€ Discount', $priceDefinition, [
            'discountScope' => 'cart',
            'discountType' => 'absolute',
            'filter' => [
                'sorterKey' => 'PRICE_ASC',
                'applierKey' => 'ALL',
                'usageKey' => 'UNLIMITED',
            ],
        ], null);
    }

    private function getDiscountPackageCollection(): DiscountPackageCollection
    {
        return new DiscountPackageCollection([
            new DiscountPackage(new LineItemQuantityCollection()),
        ]);
    }
}
