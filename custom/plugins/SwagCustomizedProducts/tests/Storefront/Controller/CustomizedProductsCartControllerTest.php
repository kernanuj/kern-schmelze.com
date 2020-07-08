<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Storefront\Controller;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Exception\LineItemNotFoundException;
use Shopware\Core\Checkout\Cart\Exception\OrderNotFoundException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Content\Rule\RuleEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\Tax\TaxDefinition;
use Swag\CustomizedProducts\Core\Checkout\Cart\Route\AddCustomizedProductsToCartRoute;
use Swag\CustomizedProducts\Core\Checkout\Cart\Route\ReOrderCustomizedProductsRoute;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Storefront\Controller\CustomizedProductsCartController;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\FileUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\HtmlEditor;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Textarea;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\TextField;
use Swag\CustomizedProducts\Template\TemplateDefinition;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;
use Symfony\Component\HttpFoundation\Request;
use function array_values;
use function random_int;
use function sprintf;

class CustomizedProductsCartControllerTest extends TestCase
{
    use ServicesTrait;

    private const TEMPLATE_DISPLAY_NAME = 'Nice template display name';
    private const OPTION_DISPLAY_NORMAL_NAME = 'Nice normal option display name';
    private const OPTION_DISPLAY_ONE_TIME_NAME = 'Nice one time surcharge option display name';
    private const OPTION_DISPLAY_HTMLEDITOR_NAME = 'Nice htmleditor option display name';
    private const TEST_TEXT_FIELD_VALUE = 'abc';
    private const TEST_TEXTAREA_VALUE = 'xyz';
    private const TEST_HTMLEDITOR_VALUE = '<p>Hello world </p><b>bold</b><i>italic</i><script>alert(1);</script><u>underline</u><strike>strike</strike>';
    private const TEST_HTMLEDITOR_PURIFIED = '<p>Hello world </p><b>bold</b><i>italic</i><u>underline</u><strike>strike</strike>';
    private const CART_TOKEN = 'test-cart';
    private const NORMAL_ITEM_NUMBER = 'CustomItemNumber';
    private const FIXTURE_DIR = __DIR__ . '/../Framework/Media/Validator/fixtures';

    /**
     * @var SalesChannelContextFactory
     */
    private $salesChannelContextFactory;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var StateMachineRegistry
     */
    private $stateMachineRegistry;

    /**
     * @var EntityRepositoryInterface
     */
    private $templateRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $this->salesChannelContextFactory = $container->get(SalesChannelContextFactory::class);
        $this->cartService = $container->get(CartService::class);
        $this->orderRepository = $container->get('order.repository');
        $this->stateMachineRegistry = $container->get(StateMachineRegistry::class);
        $this->templateRepository = $container->get(TemplateDefinition::ENTITY_NAME . '.repository');
    }

    public function testAddCustomizedProductsMissingParameter(): void
    {
        $controller = $this->createController();

        $cart = new Cart('testCart', 'token');
        $requestDataBag = new RequestDataBag();
        $request = new Request();
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);

        $this->expectException(MissingRequestParameterException::class);
        $this->expectExceptionMessage( sprintf(
            'Parameter "%s" is missing.',
            CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER
        ));
        $controller->addCustomizedProduct(
            $cart,
            $requestDataBag,
            $request,
            $salesChannelContext
        );
    }

    public function testAddCustomizedProductsMissingProduct(): void
    {
        $controller = $this->createController();

        $cart = new Cart('testCart', 'token');
        $requestDataBag = new RequestDataBag(
            [
                CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER => [
                    'id' => Uuid::randomHex(),
                ],
            ]
        );
        $request = new Request();
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);

        $this->expectException(MissingRequestParameterException::class);
        $this->expectExceptionMessage( sprintf('Parameter "%s" is missing.', 'lineItems'));
        $controller->addCustomizedProduct(
            $cart,
            $requestDataBag,
            $request,
            $salesChannelContext
        );
    }

    public function testAddCustomizedProductsMissingOptions(): void
    {
        $controller = $this->createController();

        $templateId = Uuid::randomHex();
        $optionNormalId = Uuid::randomHex();
        $optionOneTimeId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $taxId = $this->createTaxId();

        $templateData = $this->getTemplateData(
            $templateId,
            $productId,
            $taxId,
            [
                [
                    'id' => $optionNormalId,
                    'displayName' => self::OPTION_DISPLAY_NORMAL_NAME,
                    'type' => TextField::NAME,
                    'position' => 0,
                    'itemNumber' => self::NORMAL_ITEM_NUMBER,
                    'taxId' => $taxId,
                    'typeProperties' => [
                        'minLength' => 100,
                        'maxLength' => 500,
                    ],
                ],
                [
                    'id' => $optionOneTimeId,
                    'displayName' => self::OPTION_DISPLAY_ONE_TIME_NAME,
                    'type' => Textarea::NAME,
                    'position' => 1,
                    'isOneTimeSurcharge' => true,
                    'taxId' => $taxId,
                    'typeProperties' => [
                        'minLength' => 100,
                        'maxLength' => 500,
                    ],
                ],
            ]
        );

        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);

        $this->createTemplate(
            $templateId,
            $this->templateRepository,
            $salesChannelContext->getContext(),
            $templateData
        );

        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $request = $this->createAddToCartRequest($productId);
        $requestDataBag = new RequestDataBag([
            CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER => [
                'id' => $templateId,
                'options' => [],
            ],
        ]);

        $controller->addCustomizedProduct(
            $cart,
            $requestDataBag,
            $request,
            $salesChannelContext
        );

        $filledCart = $this->cartService->getCart(self::CART_TOKEN, $salesChannelContext);
        $lineItemCollection = $filledCart->getLineItems();
        static::assertCount(1, $lineItemCollection);
        static::assertNotNull($lineItemCollection->first());
        static::assertCount(1, $lineItemCollection->first()->getChildren());
        static::assertNotNull($lineItemCollection->first()->getChildren()->first());
        static::assertSame($productId, $lineItemCollection->first()->getChildren()->first()->getReferencedId());
    }

    public function testAddCustomizedProductsMissingReferenceId(): void
    {
        $controller = $this->createController();

        $cart = new Cart('testCart', 'token');
        $requestDataBag = new RequestDataBag(
            [CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER => ['options' => []]]
        );
        $request = new Request([], ['lineItems' => [['id' => Uuid::randomHex(), 'quantity' => 2]]]);
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);

        $this->expectException(MissingRequestParameterException::class);
        $this->expectExceptionMessage('Parameter "id" is missing.');
        $controller->addCustomizedProduct(
            $cart,
            $requestDataBag,
            $request,
            $salesChannelContext
        );
    }

    public function testAddCustomizedProduct(): void
    {
        $controller = $this->createController();

        $templateId = Uuid::randomHex();
        $optionNormalId = Uuid::randomHex();
        $optionOneTimeId = Uuid::randomHex();
        $optionHtmlId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $taxId = $this->createTaxId();

        $templateData = $this->getTemplateData(
            $templateId,
            $productId,
            $taxId,
            [
                [
                    'id' => $optionNormalId,
                    'displayName' => self::OPTION_DISPLAY_NORMAL_NAME,
                    'type' => TextField::NAME,
                    'position' => 0,
                    'itemNumber' => self::NORMAL_ITEM_NUMBER,
                    'taxId' => $taxId,
                    'typeProperties' => [
                        'minLength' => 100,
                        'maxLength' => 500,
                    ],
                ],
                [
                    'id' => $optionOneTimeId,
                    'displayName' => self::OPTION_DISPLAY_ONE_TIME_NAME,
                    'type' => Textarea::NAME,
                    'position' => 1,
                    'isOneTimeSurcharge' => true,
                    'taxId' => $taxId,
                    'typeProperties' => [
                        'minLength' => 100,
                        'maxLength' => 500,
                    ],
                ],
                [
                    'id' => $optionHtmlId,
                    'displayName' => self::OPTION_DISPLAY_HTMLEDITOR_NAME,
                    'type' => HtmlEditor::NAME,
                    'position' => 2,
                    'taxId' => $taxId,
                    'typeProperties' => [],
                ],
            ]
        );

        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);

        $this->templateRepository->create([$templateData], $salesChannelContext->getContext());

        $cart = $this->cartService->createNew(self::CART_TOKEN);
        $requestDataBag = $this->createRequestDataBag($templateId, $optionNormalId, $optionOneTimeId, $optionHtmlId);
        $request = $this->createAddToCartRequest($productId);

        $controller->addCustomizedProduct(
            $cart,
            $requestDataBag,
            $request,
            $salesChannelContext
        );

        $filledCart = $this->cartService->getCart(self::CART_TOKEN, $salesChannelContext);
        static::assertCount(1, $filledCart->getLineItems());
        $customProductLineItem = $filledCart->getLineItems()->first();
        static::assertNotNull($customProductLineItem);
        static::assertSame(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE, $customProductLineItem->getType());
        static::assertSame(self::TEMPLATE_DISPLAY_NAME, $customProductLineItem->getLabel());
        static::assertTrue($customProductLineItem->hasPayloadValue('productNumber'));
        static::assertSame('*', $customProductLineItem->getPayloadValue('productNumber'));
        static::assertNotNull($customProductLineItem->getDeliveryInformation());

        $children = $customProductLineItem->getChildren();
        static::assertCount(4, $children);

        $optionLineItems = $children->filterType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE);
        static::assertCount(3, $optionLineItems);

        $firstOption = $optionLineItems->first();
        static::assertNotNull($firstOption);
        static::assertSame(self::OPTION_DISPLAY_NORMAL_NAME, $firstOption->getLabel());
        static::assertSame(self::TEST_TEXT_FIELD_VALUE, $firstOption->getPayloadValue('value'));
        static::assertSame(self::NORMAL_ITEM_NUMBER, $firstOption->getPayloadValue('productNumber'));

        /** @var LineItem $secondOption */
        $secondOption = array_values($optionLineItems->getElements())[1];
        static::assertTrue($secondOption->hasPayloadValue('productNumber'));
        static::assertSame('*', $secondOption->getPayloadValue('productNumber'));

        /** @var LineItem $thirdOption */
        $thirdOption = array_values($optionLineItems->getElements())[2];
        static::assertTrue($thirdOption->hasPayloadValue('productNumber'));
        static::assertSame('*', $thirdOption->getPayloadValue('productNumber'));
        static::assertSame(self::TEST_HTMLEDITOR_PURIFIED, $thirdOption->getPayloadValue('value'));
    }

    public function testAddCustomizedProductWithUploadedFiles(): void
    {
        $controller = $this->createController();

        $templateId = Uuid::randomHex();
        $imageOptionId = Uuid::randomHex();
        $fileOptionId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $taxId = $this->createTaxId();

        $templateData = $this->getTemplateData(
            $templateId,
            $productId,
            $taxId,
            [
                [
                    'id' => $imageOptionId,
                    'displayName' => self::OPTION_DISPLAY_NORMAL_NAME,
                    'type' => ImageUpload::NAME,
                    'position' => 0,
                    'itemNumber' => self::NORMAL_ITEM_NUMBER,
                    'taxId' => $taxId,
                    'typeProperties' => [
                        'maxCount' => 1,
                        'maxFileSize' => 10,
                    ],
                ],
                [
                    'id' => $fileOptionId,
                    'displayName' => self::OPTION_DISPLAY_ONE_TIME_NAME,
                    'type' => FileUpload::NAME,
                    'position' => 1,
                    'isOneTimeSurcharge' => true,
                    'taxId' => $taxId,
                    'typeProperties' => [
                        'maxCount' => 1,
                        'maxFileSize' => 10,
                    ],
                ],
            ]
        );

        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);

        /** @var EntityRepositoryInterface $templateRepo */
        $templateRepo = $this->getContainer()->get('swag_customized_products_template.repository');
        $templateRepo->create([$templateData], $salesChannelContext->getContext());

        $cart = $this->cartService->createNew(self::CART_TOKEN);

        $requestDataBag = new RequestDataBag(
            [
                CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER => [
                    'id' => $templateId,
                    'options' => [
                        $imageOptionId => [
                            'type' => ImageUpload::NAME,
                            'media' => [
                                [
                                    'id' => Uuid::randomHex(),
                                    'filename' => 'test.jpg',
                                ],
                            ],
                        ],
                        $fileOptionId => [
                            'type' => FileUpload::NAME,
                            'media' => [
                                [
                                    'id' => Uuid::randomHex(),
                                    'filename' => 'test.pdf',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $request = new Request([], [
            'lineItems' => [
                $productId => [
                    'quantity' => 5,
                    'id' => $productId,
                    'type' => LineItem::PRODUCT_LINE_ITEM_TYPE,
                    'referencedId' => $productId,
                    'stackable' => true,
                    'removable' => true,
                ],
            ],
        ]);

        $controller->addCustomizedProduct(
            $cart,
            $requestDataBag,
            $request,
            $salesChannelContext
        );

        $filledCart = $this->cartService->getCart(self::CART_TOKEN, $salesChannelContext);
        static::assertCount(1, $filledCart->getLineItems());
        $customProductLineItem = $filledCart->getLineItems()->first();
        static::assertNotNull($customProductLineItem);
        static::assertSame(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE, $customProductLineItem->getType());
        static::assertSame(self::TEMPLATE_DISPLAY_NAME, $customProductLineItem->getLabel());
        static::assertTrue($customProductLineItem->hasPayloadValue('productNumber'));
        static::assertSame('*', $customProductLineItem->getPayloadValue('productNumber'));
        static::assertNotNull($customProductLineItem->getDeliveryInformation());

        $children = $customProductLineItem->getChildren();
        static::assertCount(3, $children);

        $optionLineItems = $children->filterType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE);
        static::assertCount(2, $optionLineItems);

        $firstOption = $optionLineItems->first();
        static::assertNotNull($firstOption);
        static::assertSame(self::OPTION_DISPLAY_NORMAL_NAME, $firstOption->getLabel());
        static::assertNotEmpty($firstOption->getPayloadValue('media'));
        static::assertArrayHasKey('mediaId', $firstOption->getPayloadValue('media')[0]);
        static::assertArrayHasKey('filename', $firstOption->getPayloadValue('media')[0]);
        static::assertSame(self::NORMAL_ITEM_NUMBER, $firstOption->getPayloadValue('productNumber'));

        /** @var LineItem $secondOption */
        $secondOption = array_values($optionLineItems->getElements())[1];
        static::assertTrue($secondOption->hasPayloadValue('productNumber'));
        static::assertSame('*', $secondOption->getPayloadValue('productNumber'));
        static::assertNotEmpty($secondOption->getPayloadValue('media'));
        static::assertArrayHasKey('mediaId', $secondOption->getPayloadValue('media')[0]);
        static::assertArrayHasKey('filename', $secondOption->getPayloadValue('media')[0]);
    }

    public function testAddCustomizedProductPrices(): void
    {
        $controller = $this->createController();

        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $taxId = $this->createTaxId();

        $templateData = [
            'id' => $templateId,
            'internalName' => 'internalTemplateName',
            'displayName' => self::TEMPLATE_DISPLAY_NAME,
            'active' => true,
            'options' => [
                [
                    'id' => $optionId,
                    'displayName' => self::OPTION_DISPLAY_NORMAL_NAME,
                    'type' => TextField::NAME,
                    'position' => 0,
                    'itemNumber' => self::NORMAL_ITEM_NUMBER,
                    'taxId' => $taxId,
                    'price' => [
                        [
                            'currencyId' => Defaults::CURRENCY,
                            'gross' => 19.99,
                            'net' => 8,
                            'linked' => false,
                        ],
                    ],
                    'typeProperties' => [
                        'minLength' => 100,
                        'maxLength' => 500,
                    ],
                ],
            ],
            'products' => [
                [
                    'id' => $productId,
                    'name' => 'Test name of a product',
                    'manufacturer' => [
                        'id' => Uuid::randomHex(),
                        'name' => 'amazing brand',
                    ],
                    'active' => true,
                    'visibilities' => [
                        [
                            'salesChannelId' => Defaults::SALES_CHANNEL,
                            'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                        ],
                    ],
                    'productNumber' => 'P1234',
                    'tax' => ['id' => $taxId],
                    'price' => [
                        [
                            'currencyId' => Defaults::CURRENCY,
                            'gross' => 5,
                            'net' => 8,
                            'linked' => false,
                        ],
                    ],
                    'stock' => 10,
                    'typeProperties' => [
                        'minLength' => 100,
                        'maxLength' => 500,
                    ],
                ],
            ],
        ];

        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);

        $this->createTemplate(
            $templateId,
            $this->templateRepository,
            $salesChannelContext->getContext(),
            $templateData
        );

        $cart = $this->cartService->createNew(self::CART_TOKEN);

        $requestDataBag = new RequestDataBag(
            [
                CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER => [
                    'id' => $templateId,
                    'options' => [
                        $optionId => [
                            'isOneTimeSurcharge' => false,
                            'value' => self::TEST_TEXT_FIELD_VALUE,
                        ],
                    ],
                ],
            ]
        );

        $request = $this->createAddToCartRequest($productId);

        $controller->addCustomizedProduct(
            $cart,
            $requestDataBag,
            $request,
            $salesChannelContext
        );

        $filledCart = $this->cartService->getCart(self::CART_TOKEN, $salesChannelContext);
        static::assertCount(1, $filledCart->getLineItems());
        $customProductsLineItem = $filledCart->getLineItems()->first();
        static::assertNotNull($customProductsLineItem);
        static::assertSame(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE, $customProductsLineItem->getType());
        static::assertSame(self::TEMPLATE_DISPLAY_NAME, $customProductsLineItem->getLabel());

        $children = $customProductsLineItem->getChildren();
        static::assertCount(2, $children);

        $optionLineItems = $children->filterType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE);
        static::assertCount(1, $optionLineItems);

        $firstOption = $optionLineItems->first();
        static::assertNotNull($firstOption);
        static::assertSame(self::OPTION_DISPLAY_NORMAL_NAME, $firstOption->getLabel());
        static::assertSame(self::TEST_TEXT_FIELD_VALUE, $firstOption->getPayloadValue('value'));
        static::assertSame(self::NORMAL_ITEM_NUMBER, $firstOption->getPayloadValue('productNumber'));
        static::assertSame(124.95, $filledCart->getPrice()->getTotalPrice());
    }

    public function testAddCustomizedProductPriceRelative(): void
    {
        $controller = $this->createController();

        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $taxId = $this->createTaxId();

        $templateData = $this->getTemplateData(
            $templateId,
            $productId,
            $taxId,
            [
                [
                    'id' => $optionId,
                    'displayName' => self::OPTION_DISPLAY_NORMAL_NAME,
                    'type' => Checkbox::NAME,
                    'position' => 0,
                    'itemNumber' => self::NORMAL_ITEM_NUMBER,
                    'percentageSurcharge' => 25.0,
                    'relativeSurcharge' => true,
                    'typeProperties' => [],
                ],
            ]
        );

        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);

        $this->templateRepository->create([$templateData], $salesChannelContext->getContext());

        $cart = $this->cartService->createNew(self::CART_TOKEN);

        $requestDataBag = new RequestDataBag(
            [
                CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER => [
                    'id' => $templateId,
                    'options' => [
                        $optionId => [
                            'isOneTimeSurcharge' => false,
                            'value' => self::TEST_TEXT_FIELD_VALUE,
                        ],
                    ],
                ],
            ]
        );

        $request = $this->createAddToCartRequest($productId);

        $controller->addCustomizedProduct(
            $cart,
            $requestDataBag,
            $request,
            $salesChannelContext
        );

        $filledCart = $this->cartService->getCart(self::CART_TOKEN, $salesChannelContext);
        $this->assertFilledCart(
            $filledCart,
            1,
            2,
            1,
            31.25
        );
    }

    public function testAddCustomizedProductPriceAdvancedRelative(): void
    {
        $controller = $this->createController();

        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $taxId = $this->createTaxId();

        $ruleId = $this->createRuleId(Context::createDefaultContext());
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);

        $templateData = $this->getTemplateData(
            $templateId,
            $productId,
            $taxId,
            [
                [
                    'id' => $optionId,
                    'displayName' => self::OPTION_DISPLAY_NORMAL_NAME,
                    'type' => Checkbox::NAME,
                    'position' => 0,
                    'itemNumber' => self::NORMAL_ITEM_NUMBER,
                    'percentageSurcharge' => 25.0,
                    'relativeSurcharge' => true,
                    'advancedSurcharge' => true,
                    'prices' => [
                        [
                            'percentageSurcharge' => 5.0,
                            'ruleId' => $ruleId,
                        ],
                    ],
                    'typeProperties' => [],
                ],
            ]
        );

        $this->createTemplate(
            $templateId,
            $this->templateRepository,
            $salesChannelContext->getContext(),
            $templateData
        );

        $cart = $this->cartService->createNew(self::CART_TOKEN);

        $requestDataBag = new RequestDataBag(
            [
                CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER => [
                    'id' => $templateId,
                    'options' => [
                        $optionId => [
                            'value' => self::TEST_TEXT_FIELD_VALUE,
                        ],
                    ],
                ],
            ]
        );

        $request = $this->createAddToCartRequest($productId);

        $controller->addCustomizedProduct(
            $cart,
            $requestDataBag,
            $request,
            $salesChannelContext
        );

        $filledCart = $this->cartService->getCart(self::CART_TOKEN, $salesChannelContext);
        $this->assertFilledCart(
            $filledCart,
            1,
            2,
            1,
            26.25
        );
    }

    public function testCustomizedProductPrices(): void
    {
        $controller = $this->createController();

        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $taxId = $this->createTaxId();

        $ruleId = $this->createRuleId(Context::createDefaultContext());
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);

        $templateData = $this->getTemplateData(
            $templateId,
            $productId,
            $taxId,
            [
                [
                    'id' => $optionId,
                    'displayName' => self::OPTION_DISPLAY_NORMAL_NAME,
                    'type' => Checkbox::NAME,
                    'position' => 0,
                    'itemNumber' => self::NORMAL_ITEM_NUMBER,
                    'taxId' => $taxId,
                    'typeProperties' => [],
                    'price' => [
                        [
                            'currencyId' => Defaults::CURRENCY,
                            'gross' => 0.88,
                            'net' => 0.49,
                            'linked' => false,
                        ],
                    ],
                    'advancedSurcharge' => true,
                    'prices' => [
                        [
                            'ruleId' => $ruleId,
                            'price' => [
                                [
                                    'currencyId' => Defaults::CURRENCY,
                                    'gross' => 17.88,
                                    'net' => 16.49,
                                    'linked' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->templateRepository->create([$templateData], $salesChannelContext->getContext());

        $cart = $this->cartService->createNew(self::CART_TOKEN);

        $requestDataBag = new RequestDataBag(
            [
                CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER => [
                    'id' => $templateId,
                    'options' => [
                        $optionId => [
                            'value' => self::TEST_TEXT_FIELD_VALUE,
                        ],
                    ],
                ],
            ]
        );

        $request = $this->createAddToCartRequest($productId);
        $controller->addCustomizedProduct(
            $cart,
            $requestDataBag,
            $request,
            $salesChannelContext
        );

        $filledCart = $this->cartService->getCart(self::CART_TOKEN, $salesChannelContext);
        $this->assertFilledCart(
            $filledCart,
            1,
            2,
            1,
            114.4
        );
    }

    public function testReorderCustomizedProductInvalidOrderId(): void
    {
        $controller = $this->createController();
        $originalCart = new Cart('cart-name', Uuid::randomHex());
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);
        $orderId = 'invalid-order-id';

        static::expectException(InvalidUuidException::class);
        static::expectExceptionMessage('Value is not a valid UUID: ' . $orderId);

        $controller->reorderCustomizedProduct(
            $orderId,
            $originalCart,
            new Request([], []),
            $salesChannelContext
        );
    }

    public function testReorderCustomizedProductOrderEntityNotFound(): void
    {
        $controller = $this->createController();
        $originalCart = new Cart('cart-name', Uuid::randomHex());
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);
        $orderId = Uuid::randomHex();

        static::expectException(OrderNotFoundException::class);
        static::expectExceptionMessage('Order with id "' . $orderId . '" not found.');

        $controller->reorderCustomizedProduct(
            $orderId,
            $originalCart,
            new Request([], []),
            $salesChannelContext
        );
    }

    public function testReorderCustomizedProductOrderEntityMissingLineItems(): void
    {
        $orderId = Uuid::randomHex();
        $controller = $this->createController();
        $originalCart = new Cart('cart-name', Uuid::randomHex());
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);
        $orderData = $this->getOrderData($orderId, $salesChannelContext->getContext());
        unset($orderData[0]['lineItems']);

        $salesChannelContext->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $c) use ($orderData): void {
            $this->orderRepository->create($orderData, $c);
        });

        static::expectException(LineItemNotFoundException::class);
        static::expectExceptionMessage('Line item with identifier ' . $orderId . ' not found.');

        $controller->reorderCustomizedProduct(
            $orderId,
            $originalCart,
            new Request([], []),
            $salesChannelContext
        );
    }

    public function testReorderCustomizedProduct(): void
    {
        $taxId = $this->createTaxId();
        $orderId = Uuid::randomHex();
        $lineItemId = Uuid::randomHex();
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $productId = $this->createProduct($taxId);
        $controller = $this->createController();
        $originalCart = new Cart('cart-name', Uuid::randomHex());
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);

        $templateData = $this->getTemplateData(
            $templateId,
            $productId,
            $taxId,
            [
                [
                    'id' => $optionId,
                    'displayName' => self::OPTION_DISPLAY_NORMAL_NAME,
                    'type' => TextField::NAME,
                    'position' => 0,
                    'itemNumber' => self::NORMAL_ITEM_NUMBER,
                    'taxId' => $taxId,
                    'typeProperties' => [],
                ],
            ]
        );

        $this->createTemplate(
            $templateId,
            $this->templateRepository,
            $salesChannelContext->getContext(),
            $templateData
        );

        $orderData = $this->getOrderData($orderId, $salesChannelContext->getContext(), $lineItemId, $templateId, $productId, $optionId);

        $salesChannelContext->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $c) use ($orderData): void {
            $this->orderRepository->create($orderData, $c);
        });

        $controller->reorderCustomizedProduct(
            $orderId,
            $originalCart,
            new Request([], []),
            $salesChannelContext
        );

        $lineItemCollection = $originalCart->getLineItems();
        static::assertCount(1, $lineItemCollection);
        $reorderedLineItem = $lineItemCollection->get($lineItemId);
        static::assertInstanceOf(LineItem::class, $reorderedLineItem);
        static::assertSame($reorderedLineItem->getLabel(), 'test');
    }

    private function createController(): CustomizedProductsCartController
    {
        $container = $this->getContainer();
        $controller = new CustomizedProductsCartController(
            $container->get(AddCustomizedProductsToCartRoute::class),
            $container->get(ReOrderCustomizedProductsRoute::class)
        );

        $controller->setContainer($container);

        return $controller;
    }

    private function createAddToCartRequest(string $productId): Request
    {
        $request = new Request([], [
            'lineItems' => [
                $productId => [
                    'quantity' => 5,
                    'id' => $productId,
                    'type' => LineItem::PRODUCT_LINE_ITEM_TYPE,
                    'referencedId' => $productId,
                    'stackable' => true,
                    'removable' => true,
                ],
            ],
        ]);

        return $request;
    }

    private function createRequestDataBag(string $templateId, string $optionNormalId, string $optionOneTimeId, string $optionHtmlId): RequestDataBag
    {
        $requestDataBag = new RequestDataBag(
            [
                CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER => [
                    'id' => $templateId,
                    'options' => [
                        $optionNormalId => [
                            'isOneTimeSurcharge' => false,
                            'value' => self::TEST_TEXT_FIELD_VALUE,
                        ],
                        $optionOneTimeId => [
                            'isOneTimeSurcharge' => true,
                            'value' => self::TEST_TEXTAREA_VALUE,
                        ],
                        $optionHtmlId => [
                            'isOneTimeSurcharge' => false,
                            'value' => self::TEST_HTMLEDITOR_VALUE,
                        ],
                    ],
                ],
            ]
        );

        return $requestDataBag;
    }

    private function createTaxId(): string
    {
        /** @var EntityRepositoryInterface $taxRepo */
        $taxRepo = $this->getContainer()->get(TaxDefinition::ENTITY_NAME . '.repository');
        $taxId = Uuid::randomHex();
        $taxData = [
            [
                'id' => $taxId,
                'taxRate' => 19.0,
                'name' => 'testTaxRate',
            ],
        ];

        $taxRepo->create($taxData, Context::createDefaultContext());

        return $taxId;
    }

    private function createRuleId(Context $context): string
    {
        /** @var EntityRepositoryInterface $ruleRepo */
        $ruleRepo = $this->getContainer()->get(RuleDefinition::ENTITY_NAME . '.repository');

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter(
                'name',
                'All customers'
            )
        );

        /** @var RuleEntity|null $rule */
        $rule = $ruleRepo->search($criteria, $context)->first();

        static::assertNotNull($rule);
        static::assertInstanceOf(RuleEntity::class, $rule);

        return $rule->getId();
    }

    private function getTemplateData(string $templateId, string $productId, string $taxId, array $optionData = []): array
    {
        $templateData = [
            'id' => $templateId,
            'internalName' => 'internalTemplateName',
            'displayName' => self::TEMPLATE_DISPLAY_NAME,
            'active' => true,
        ];

        if (!empty($productId)) {
            $templateData['products'] = [
                [
                    'id' => $productId,
                    'name' => 'Test name of a product',
                    'manufacturer' => [
                        'id' => Uuid::randomHex(),
                        'name' => 'amazing brand',
                    ],
                    'active' => true,
                    'visibilities' => [
                        [
                            'salesChannelId' => Defaults::SALES_CHANNEL,
                            'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                        ],
                    ],
                    'productNumber' => 'P1234',
                    'tax' => ['id' => $taxId],
                    'price' => [
                        [
                            'currencyId' => Defaults::CURRENCY,
                            'gross' => 5,
                            'net' => 8,
                            'linked' => false,
                        ],
                    ],
                    'stock' => 10,
                ],
            ];
        }

        if (!empty($optionData)) {
            $templateData['options'] = $optionData;
        }

        return $templateData;
    }

    private function assertFilledCart(
        Cart $filledCart,
        int $lineItemCount,
        int $customProductsLineItemChildren,
        int $customProductsProductLineItemChildren,
        float $price
    ): void {
        static::assertCount($lineItemCount, $filledCart->getLineItems());
        $customProductsLineItem = $filledCart->getLineItems()->first();
        static::assertNotNull($customProductsLineItem);
        static::assertSame(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE, $customProductsLineItem->getType());
        static::assertSame(self::TEMPLATE_DISPLAY_NAME, $customProductsLineItem->getLabel());

        $children = $customProductsLineItem->getChildren();
        static::assertCount($customProductsLineItemChildren, $children);

        $optionLineItems = $children->filterType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE);
        static::assertCount($customProductsProductLineItemChildren, $optionLineItems);

        $firstOption = $optionLineItems->first();
        static::assertNotNull($firstOption);
        static::assertSame(self::OPTION_DISPLAY_NORMAL_NAME, $firstOption->getLabel());
        static::assertSame(self::TEST_TEXT_FIELD_VALUE, $firstOption->getPayloadValue('value'));
        static::assertSame(self::NORMAL_ITEM_NUMBER, $firstOption->getPayloadValue('productNumber'));
        static::assertSame($price, $filledCart->getPrice()->getTotalPrice());
    }

    private function getOrderData(
        string $orderId,
        Context $context,
        ?string $lineItemId = null,
        ?string $referenceId = null,
        ?string $productId = null,
        ?string $optionId = null
    ): array {
        $addressId = Uuid::randomHex();
        $orderLineItemId = Uuid::randomHex();
        $countryStateId = Uuid::randomHex();
        $salutation = $this->getValidSalutationId();
        $productId = $productId ?? Uuid::randomHex();

        return [
            [
                'id' => $orderId,
                'orderDateTime' => (new DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                'price' => new CartPrice(10, 10, 10, new CalculatedTaxCollection(), new TaxRuleCollection(), CartPrice::TAX_STATE_NET),
                'shippingCosts' => new CalculatedPrice(10, 10, new CalculatedTaxCollection(), new TaxRuleCollection()),
                'stateId' => $this->stateMachineRegistry->getInitialState(OrderStates::STATE_MACHINE, $context)->getId(),
                'paymentMethodId' => $this->getValidPaymentMethodId(),
                'currencyId' => Defaults::CURRENCY,
                'currencyFactor' => 1,
                'salesChannelId' => Defaults::SALES_CHANNEL,
                'lineItems' => [
                    [
                        'id' => $lineItemId ?? $orderLineItemId,
                        'identifier' => $lineItemId ?? $orderLineItemId,
                        'referencedId' => $referenceId ?? Uuid::randomHex(),
                        'quantity' => 1,
                        'type' => CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
                        'label' => 'test',
                        'price' => new CalculatedPrice(10, 10, new CalculatedTaxCollection(), new TaxRuleCollection()),
                        'priceDefinition' => new QuantityPriceDefinition(10, new TaxRuleCollection(), 2),
                        'good' => true,
                    ],
                    [
                        'id' => $productId,
                        'parentId' => $lineItemId ?? $orderLineItemId,
                        'identifier' => $productId,
                        'referencedId' => $productId,
                        'quantity' => 1,
                        'type' => LineItem::PRODUCT_LINE_ITEM_TYPE,
                        'label' => 'test',
                        'price' => new CalculatedPrice(10, 10, new CalculatedTaxCollection(), new TaxRuleCollection()),
                        'priceDefinition' => new QuantityPriceDefinition(10, new TaxRuleCollection(), 2),
                        'good' => true,
                    ],
                    [
                        'id' => $optionId ?? Uuid::randomHex(),
                        'parentId' => $lineItemId ?? $orderLineItemId,
                        'identifier' => $optionId ?? Uuid::randomHex(),
                        'referencedId' => $optionId ?? Uuid::randomHex(),
                        'quantity' => 1,
                        'type' => CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
                        'label' => 'test',
                        'price' => new CalculatedPrice(10, 10, new CalculatedTaxCollection(), new TaxRuleCollection()),
                        'priceDefinition' => new QuantityPriceDefinition(10, new TaxRuleCollection(), 2),
                        'good' => true,
                        'payload' => [
                            'value' => 'on',
                        ],
                    ],
                ],
                'deepLinkCode' => 'BwvdEInxOHBbwfRw6oHF1Q_orfYeo9RY',
                'orderCustomer' => [
                    'email' => 'test@example.com',
                    'firstName' => 'Noe',
                    'lastName' => 'Hill',
                    'salutationId' => $salutation,
                    'title' => 'Doc',
                    'customerNumber' => 'Test',
                    'customer' => [
                        'email' => 'test@example.com',
                        'firstName' => 'Noe',
                        'lastName' => 'Hill',
                        'salutationId' => $salutation,
                        'title' => 'Doc',
                        'customerNumber' => 'Test',
                        'guest' => true,
                        'group' => ['name' => 'testse2323'],
                        'defaultPaymentMethodId' => $this->getValidPaymentMethodId(),
                        'salesChannelId' => Defaults::SALES_CHANNEL,
                        'defaultBillingAddressId' => $addressId,
                        'defaultShippingAddressId' => $addressId,
                        'addresses' => [
                            [
                                'id' => $addressId,
                                'salutationId' => $salutation,
                                'firstName' => 'Floy',
                                'lastName' => 'Glover',
                                'zipcode' => '59438-0403',
                                'city' => 'Stellaberg',
                                'street' => 'street',
                                'countryStateId' => $countryStateId,
                                'country' => [
                                    'name' => 'kasachstan',
                                    'id' => $this->getValidCountryId(),
                                    'states' => [
                                        [
                                            'id' => $countryStateId,
                                            'name' => 'oklahoma',
                                            'shortCode' => 'OH',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'billingAddressId' => $addressId,
                'addresses' => [
                    [
                        'salutationId' => $salutation,
                        'firstName' => 'Floy',
                        'lastName' => 'Glover',
                        'zipcode' => '59438-0403',
                        'city' => 'Stellaberg',
                        'street' => 'street',
                        'countryId' => $this->getValidCountryId(),
                        'id' => $addressId,
                    ],
                ],
            ],
        ];
    }

    private function createProduct(string $taxId): string
    {
        $productId = Uuid::randomHex();

        /** @var EntityRepositoryInterface $productRepository */
        $productRepository = $this->getContainer()->get('product.repository');
        $productRepository->create([
            [
                'id' => $productId,
                'stock' => random_int(1, 5),
                'taxId' => $taxId,
                'price' => [
                    'net' => [
                        'currencyId' => Defaults::CURRENCY,
                        'net' => 74.49,
                        'gross' => 89.66,
                        'linked' => true,
                    ],
                ],
                'productNumber' => 'test-234',
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'name' => 'example-product',
                    ],
                ],
            ],
        ], Context::createDefaultContext());

        return $productId;
    }
}
