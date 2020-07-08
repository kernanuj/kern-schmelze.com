<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\Exception\MissingLineItemPriceException;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsNotAvailableError;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartProcessor;
use Swag\CustomizedProducts\Template\Exception\NoProductException;

class CustomizedProductsCartProcessorTest extends TestCase
{
    use IntegrationTestBehaviour;

    private const CART_TOKEN = 'test-töken';
    private const CART_TO_CALCULATE_TOKEN = 'test-to-calculate';
    private const SALES_CHANNEL_TOKEN = 'sales-channel-token';

    /**
     * @var CustomizedProductsCartProcessor
     */
    private $processor;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var SalesChannelContextFactory
     */
    private $salesChannelContextFactory;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $this->processor = $container->get(CustomizedProductsCartProcessor::class);
        $this->cartService = $container->get(CartService::class);
        $this->salesChannelContextFactory = $container->get(SalesChannelContextFactory::class);
    }

    public function testThatProcessExitsIfLineItemHasNoReferencedId(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(self::SALES_CHANNEL_TOKEN, Defaults::SALES_CHANNEL);
        $templateLineItemId = Uuid::randomHex();

        $customizedProductsLineItem = new LineItem(
            $templateLineItemId,
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $customizedProductsLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            Uuid::randomHex()
        );

        $cart->add($customizedProductsLineItem);

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );

        $errorCollection = $toCalculate->getErrors();
        static::assertCount(0, $toCalculate->getLineItems());
        static::assertCount(1, $errorCollection);
        $error = $errorCollection->first();
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $error);
        static::assertSame([
            'id' => $templateLineItemId,
        ], $error->getParameters());
        static::assertFalse($error->blockOrder());
        static::assertSame(Error::LEVEL_ERROR, $error->getLevel());
    }

    public function testThatProcessRemovesLineItemsWithoutConfigurationHash(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(self::SALES_CHANNEL_TOKEN, Defaults::SALES_CHANNEL);

        $customizedProductsLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );

        $cart->add($customizedProductsLineItem);

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );

        $errorCollection = $toCalculate->getErrors();
        static::assertCount(0, $toCalculate->getLineItems());
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testThatProcessGroupsSameConfigurationHashes(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(self::SALES_CHANNEL_TOKEN, Defaults::SALES_CHANNEL);

        $productLineItem = new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );
        $productLineItem->setPriceDefinition(
            new QuantityPriceDefinition(
                100.0,
                new TaxRuleCollection(),
                2,
                1,
                true
            )
        );

        $configurationHash = Uuid::randomHex();
        $firstLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            Uuid::randomHex()
        );
        $firstLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );
        $firstLineItem->addChild($productLineItem);
        $cart->add($firstLineItem);

        $secondLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            Uuid::randomHex()
        );
        $secondLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );
        $secondLineItem->addChild($productLineItem);
        $cart->add($secondLineItem);

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );

        $lineItemCollection = $toCalculate->getLineItems();
        static::assertCount(1, $lineItemCollection);
        $lineItem = $lineItemCollection->first();
        static::assertInstanceOf(LineItem::class, $lineItem);
        static::assertSame(2, $lineItem->getQuantity());
    }

    public function testThatProcessWithoutProductThrowsException(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(self::SALES_CHANNEL_TOKEN, Defaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();

        $configurationHash = Uuid::randomHex();
        $firstLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $firstLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );
        $cart->add($firstLineItem);

        $this->expectException(NoProductException::class);
        $this->expectExceptionMessage('The template with the ID ' . $templateId . ' has no product');

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );
    }

    public function testProcessProductWithoutPriceDefinitionThrowsException(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(self::SALES_CHANNEL_TOKEN, Defaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $productLineItemId = Uuid::randomHex();

        $productLineItem = new LineItem(
            $productLineItemId,
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );

        $configurationHash = Uuid::randomHex();
        $firstLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $firstLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );
        $firstLineItem->addChild($productLineItem);
        $cart->add($firstLineItem);

        $this->expectException(MissingLineItemPriceException::class);
        $this->expectExceptionMessage('Line item ' . $productLineItemId . ' contains no price definition or already calculated price.');

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );
    }

    public function testProcessCalculateOptionPriceWithoutPriceDefinition(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(self::SALES_CHANNEL_TOKEN, Defaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $productLineItemId = Uuid::randomHex();

        $configurationHash = Uuid::randomHex();
        $customProductsTemplateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $customProductsTemplateLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );

        $productLineItem = new LineItem(
            $productLineItemId,
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );
        $productLineItem->setPriceDefinition(
            new QuantityPriceDefinition(
                100.0,
                new TaxRuleCollection(),
                2,
                1,
                true
            )
        );
        $customProductsTemplateLineItem->addChild($productLineItem);

        $customizedProductsOptionLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        $customProductsTemplateLineItem->addChild($customizedProductsOptionLineItem);

        $cart->add($customProductsTemplateLineItem);

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );

        $lineItemCollection = $toCalculate->getLineItems();
        static::assertCount(1, $lineItemCollection);
        $lineItem = $lineItemCollection->first();
        static::assertInstanceOf(LineItem::class, $lineItem);
        $optionLineItems = $lineItem->getChildren()->filterType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE);
        static::assertCount(1, $optionLineItems);
        static::assertNull($optionLineItems->getPrices()->first());
    }

    public function testProcessThrowsExceptionWithUnsupportedPriceDefinition(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(self::SALES_CHANNEL_TOKEN, Defaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $productLineItemId = Uuid::randomHex();

        $configurationHash = Uuid::randomHex();
        $customProductsTemplateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $customProductsTemplateLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );

        $productLineItem = new LineItem(
            $productLineItemId,
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );
        $priceDefinition = new QuantityPriceDefinition(100.0, new TaxRuleCollection(), 2, 1, true);
        $productLineItem->setPriceDefinition($priceDefinition);
        $customProductsTemplateLineItem->addChild($productLineItem);

        $optionLineItemId = Uuid::randomHex();
        $customizedProductsOptionLineItem = new LineItem(
            $optionLineItemId,
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        $customizedProductsOptionLineItem->setPriceDefinition(new DummyPriceDefinition());
        $customProductsTemplateLineItem->addChild($customizedProductsOptionLineItem);

        $cart->add($customProductsTemplateLineItem);

        $this->expectException(MissingLineItemPriceException::class);
        $this->expectExceptionMessage('Line item ' . $optionLineItemId . ' contains no price definition or already calculated price.');

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );
    }

    public function testProcessCalculateOptionPriceWithChildren(): void
    {
        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $toCalculate = $this->cartService->createNew(self::CART_TO_CALCULATE_TOKEN);
        $salesChannelContext = $this->salesChannelContextFactory->create(self::SALES_CHANNEL_TOKEN, Defaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $productLineItemId = Uuid::randomHex();

        $configurationHash = Uuid::randomHex();
        $customProductsTemplateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $customProductsTemplateLineItem->setPayloadValue(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            $configurationHash
        );

        $productLineItem = new LineItem(
            $productLineItemId,
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );
        $priceDefinition = new QuantityPriceDefinition(100.0, new TaxRuleCollection(), 2, 1, true);
        $productLineItem->setPriceDefinition($priceDefinition);
        $customProductsTemplateLineItem->addChild($productLineItem);

        $customizedProductsOptionLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        $customizedProductsOptionLineItem->setPriceDefinition($priceDefinition);
        $customizedProductsOptionValueLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
        );
        $customizedProductsOptionValueLineItem->setPriceDefinition($priceDefinition);
        $customizedProductsOptionLineItem->addChild($customizedProductsOptionValueLineItem);
        $customProductsTemplateLineItem->addChild($customizedProductsOptionLineItem);

        $cart->add($customProductsTemplateLineItem);

        $this->processor->process(
            new CartDataCollection(),
            $cart,
            $toCalculate,
            $salesChannelContext,
            new CartBehavior()
        );

        $lineItemCollection = $toCalculate->getLineItems();
        static::assertCount(1, $lineItemCollection);
        $lineItem = $lineItemCollection->first();
        static::assertInstanceOf(LineItem::class, $lineItem);
        $optionLineItems = $lineItem->getChildren()->filterType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE);
        static::assertCount(1, $optionLineItems);
        $optionLineItem = $optionLineItems->first();
        static::assertInstanceOf(LineItem::class, $optionLineItem);
        static::assertInstanceOf(CalculatedPrice::class, $optionLineItem->getPrice());
        static::assertSame(100.0, $optionLineItem->getPrice()->getTotalPrice());
        $optionValueLineItems = $optionLineItem->getChildren()->filterType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE);
        static::assertCount(1, $optionValueLineItems);
        $optionValueLineItem = $optionValueLineItems->first();
        static::assertInstanceOf(LineItem::class, $optionValueLineItem);
        static::assertInstanceOf(CalculatedPrice::class, $optionValueLineItem->getPrice());
        static::assertSame(100.0, $optionValueLineItem->getPrice()->getTotalPrice());
    }
}
