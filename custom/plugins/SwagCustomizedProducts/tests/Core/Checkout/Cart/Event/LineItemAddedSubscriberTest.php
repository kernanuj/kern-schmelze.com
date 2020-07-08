<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout\Cart\Event;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\Event\LineItemAddedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Promotion\Cart\PromotionProcessor;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Core\Checkout\Cart\Event\LineItemAddedSubscriber;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Storefront\Controller\CustomizedProductsCartController;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\TextField;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class LineItemAddedSubscriberTest extends TestCase
{
    use ServicesTrait;

    private const TEMPLATE_INTERNAL_NAME = 'internalTemplateName';
    private const TEMPLATE_DISPLAY_NAME = 'Nice template display name';
    private const OPTION_DISPLAY_NAME = 'Nice option display name';
    private const TEST_PRODUCT_NAME = 'Test name of a product';

    /**
     * @var string
     */
    private $productId;

    /**
     * @var SalesChannelContext
     */
    private $salesChannelContext;

    /**
     * @var SalesChannelRepositoryInterface
     */
    private $salesChannelProductRepository;

    /**
     * @var string
     */
    private $normalProductId;

    protected function setUp(): void
    {
        /** @var EntityRepository $templateRepository */
        $templateRepository = $this->getContainer()->get('swag_customized_products_template.repository');
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $this->productId = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            $templateRepository,
            Context::createDefaultContext(),
            [
                'options' => [
                    [
                        'id' => $optionId,
                        'displayName' => self::OPTION_DISPLAY_NAME,
                        'type' => TextField::NAME,
                        'position' => 0,
                        'typeProperties' => [],
                    ],
                ],
                'products' => [
                    [
                        'id' => $this->productId,
                        'name' => self::TEST_PRODUCT_NAME,
                        'manufacturer' => [
                            'id' => Uuid::randomHex(),
                            'name' => 'amazing brand',
                        ],
                        'productNumber' => 'CP1234',
                        'tax' => ['id' => Uuid::randomHex(), 'taxRate' => 19, 'name' => 'tax'],
                        'price' => [
                            [
                                'currencyId' => Defaults::CURRENCY,
                                'gross' => 10,
                                'net' => 12,
                                'linked' => false,
                            ],
                        ],
                        'stock' => 10,
                        'visibilities' => [
                            [
                                'salesChannelId' => Defaults::SALES_CHANNEL,
                                'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                            ],
                        ],
                    ],
                ],
            ]
        );

        /* @var SalesChannelRepositoryInterface $productRepository */
        $this->salesChannelProductRepository = $this->getContainer()->get('sales_channel.product.repository');

        $this->normalProductId = Uuid::randomHex();
        /** @var EntityRepositoryInterface $productRepo */
        $productRepo = $this->getContainer()->get('product.repository');
        $productRepo->create([
            [
                'id' => $this->normalProductId,
                'name' => self::TEST_PRODUCT_NAME,
                'manufacturer' => [
                    'id' => Uuid::randomHex(),
                    'name' => 'amazing brand',
                ],
                'productNumber' => 'P1234',
                'tax' => ['id' => Uuid::randomHex(), 'taxRate' => 19, 'name' => 'tax'],
                'price' => [
                    [
                        'currencyId' => Defaults::CURRENCY,
                        'gross' => 10,
                        'net' => 12,
                        'linked' => false,
                    ],
                ],
                'stock' => 10,
            ],
        ], Context::createDefaultContext());

        /** @var SalesChannelContextFactory $salesChannelContextFactory */
        $salesChannelContextFactory = $this->getContainer()->get(SalesChannelContextFactory::class);
        $this->salesChannelContext = $salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);
    }

    public function testOnLineItemAddedToCartWithCustomProduct(): void
    {
        $subscriber = new LineItemAddedSubscriber($this->salesChannelProductRepository);

        $lineItemUuid = Uuid::randomHex();
        $lineItem = new LineItem($lineItemUuid, CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE, $this->productId);
        $lineItem->setRemovable(true);

        $cart = new Cart('my cart', 'mytoken');
        $cart->addLineItems(new LineItemCollection([$lineItem]));

        $event = new LineItemAddedEvent(
            $lineItem,
            $cart,
            $this->salesChannelContext
        );

        $subscriber->onLineItemAddedToCart($event);

        /** @var Error $error */
        $error = $event->getCart()->getErrors()->first();
        static::assertCount(0, $event->getCart()->getLineItems(), 'LineItem is not removed');
        static::assertCount(1, $event->getCart()->getErrors());
        static::assertSame('product-not-found' . $this->productId, $error->getId());
    }

    public function testOnLineItemAddedToCartWithCustomProductContainingExtension(): void
    {
        $subscriber = new LineItemAddedSubscriber($this->salesChannelProductRepository);

        $lineItemUuid = Uuid::randomHex();
        $lineItem = new LineItem($lineItemUuid, CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE, $this->productId);
        $lineItem->setRemovable(true);
        $lineItem->addExtension(CustomizedProductsCartController::ADD_TO_CART_IDENTIFIER, new DummyExtension());

        $cart = new Cart('my cart', 'mytoken');
        $cart->addLineItems(new LineItemCollection([$lineItem]));

        $event = new LineItemAddedEvent(
            $lineItem,
            $cart,
            $this->salesChannelContext
        );

        $subscriber->onLineItemAddedToCart($event);

        static::assertCount(1, $event->getCart()->getLineItems(), 'LineItem is removed');
        static::assertCount(0, $event->getCart()->getErrors());
    }

    public function testOnLineItemAddedToCartWithNormalProduct(): void
    {
        $subscriber = new LineItemAddedSubscriber($this->salesChannelProductRepository);

        $lineItemUuid = Uuid::randomHex();
        $lineItem = new LineItem($lineItemUuid, 'product', $this->normalProductId);
        $lineItem->setRemovable(true);

        $cart = new Cart('my cart', 'mytoken');
        $cart->addLineItems(new LineItemCollection([$lineItem]));

        $event = new LineItemAddedEvent(
            $lineItem,
            $cart,
            $this->salesChannelContext
        );

        $subscriber->onLineItemAddedToCart($event);
        static::assertCount(1, $event->getCart()->getLineItems(), 'LineItem is removed');
    }

    public function testOnLineItemAddedToCartWithPromotion(): void
    {
        $subscriber = new LineItemAddedSubscriber($this->salesChannelProductRepository);

        $lineItemUuid = Uuid::randomHex();
        $lineItem = new LineItem($lineItemUuid, PromotionProcessor::LINE_ITEM_TYPE, $this->normalProductId);
        $lineItem->setRemovable(true);

        $cart = new Cart('my cart', 'mytoken');
        $cart->addLineItems(new LineItemCollection([$lineItem]));

        $event = new LineItemAddedEvent(
            $lineItem,
            $cart,
            $this->salesChannelContext
        );

        $subscriber->onLineItemAddedToCart($event);
        $lineItemCollection = $event->getCart()->getLineItems();
        $first = $lineItemCollection->first();
        static::assertNotNull($first, 'Promotion got removed');
        static::assertSame(PromotionProcessor::LINE_ITEM_TYPE, $first->getType());
    }

    public function testOnLineItemAddedToCartWithAddToCartExtension(): void
    {
        $subscriber = new LineItemAddedSubscriber($this->salesChannelProductRepository);

        $lineItemUuid = Uuid::randomHex();
        $lineItem = new LineItem($lineItemUuid, CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE, $this->normalProductId);
        $lineItem->setRemovable(true);
        $lineItem->addExtension(CustomizedProductsCartController::ADD_TO_CART_IDENTIFIER, null);

        $cart = new Cart('my cart', 'mytoken');
        $cart->addLineItems(new LineItemCollection([$lineItem]));

        $event = new LineItemAddedEvent(
            $lineItem,
            $cart,
            $this->salesChannelContext
        );

        $subscriber->onLineItemAddedToCart($event);
        static::assertCount(1, $event->getCart()->getLineItems(), 'LineItem is removed');
    }

    public function testOnLineItemAddedToCartWithInvalidProduct(): void
    {
        $subscriber = new LineItemAddedSubscriber($this->salesChannelProductRepository);

        $lineItemUuid = Uuid::randomHex();
        $lineItem = new LineItem($lineItemUuid, CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE, Uuid::randomHex());
        $lineItem->setRemovable(true);

        $cart = new Cart('my cart', 'mytoken');
        $cart->addLineItems(new LineItemCollection([$lineItem]));

        $event = new LineItemAddedEvent(
            $lineItem,
            $cart,
            $this->salesChannelContext
        );

        $subscriber->onLineItemAddedToCart($event);
        static::assertCount(1, $event->getCart()->getLineItems(), 'LineItem is removed');

        $lineItem = new LineItem($lineItemUuid, CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE);

        $cart = new Cart('my cart', 'mytoken');
        $cart->addLineItems(new LineItemCollection([$lineItem]));

        $event = new LineItemAddedEvent(
            $lineItem,
            $cart,
            $this->salesChannelContext
        );

        $subscriber->onLineItemAddedToCart($event);

        static::assertCount(1, $event->getCart()->getLineItems(), 'LineItem is removed');
    }
}
