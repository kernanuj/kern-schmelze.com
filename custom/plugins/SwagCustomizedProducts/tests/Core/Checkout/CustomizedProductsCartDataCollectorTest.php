<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\Cart\ProductCartProcessor;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\Tax\TaxDefinition;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsNotAvailableError;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\TemplateDefinition;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class CustomizedProductsCartDataCollectorTest extends TestCase
{
    use ServicesTrait;

    private const TEMPLATE_NAME = 'tea-cup-template';
    private const REPOSITORY_POSTFIX = '.repository';

    /**
     * @var CustomizedProductsCartDataCollector
     */
    private $cartDataCollector;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var SalesChannelContextFactory
     */
    private $salesChannelContextFactory;

    /**
     * @var EntityRepositoryInterface
     */
    private $templateRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $this->cartDataCollector = $container->get(CustomizedProductsCartDataCollector::class);
        $this->cartService = $container->get(CartService::class);
        $this->salesChannelContextFactory = $container->get(SalesChannelContextFactory::class);
        $this->templateRepository = $container->get(TemplateDefinition::ENTITY_NAME . self::REPOSITORY_POSTFIX);
    }

    public function testCollectWithCartBehaviourRecalculation(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', Defaults::SALES_CHANNEL);
        $behavior = new CartBehavior([ProductCartProcessor::SKIP_PRODUCT_RECALCULATION => true]);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            $behavior
        );

        static::assertEmpty($cart->getErrors());
    }

    public function testCollectWithoutCustomProductsLineItems(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', Defaults::SALES_CHANNEL);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            LineITem::PRODUCT_LINE_ITEM_TYPE
        );
        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        static::assertEmpty($cart->getErrors());
    }

    public function testCollectTemplateWithoutProductAddsErrorToCart(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', Defaults::SALES_CHANNEL);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            Uuid::randomHex()
        );
        $templateLineItem->setRemovable(true);
        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectWithWrongReferencedIdAddsErrorToCart(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', Defaults::SALES_CHANNEL);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            Uuid::randomHex()
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE
        ));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectCustomProductUnavailableIfProductEntityNotFound(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', Defaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            $context->getContext()
        );

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            Uuid::randomHex()
        ));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectCustomProductUnavailableIfProductEntityDoesntHaveTemplateExtension(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', Defaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $this->templateRepository->create([
            [
                'id' => $templateId,
                'active' => true,
                'internalName' => self::TEMPLATE_NAME,
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'displayName' => self::TEMPLATE_NAME,
                    ],
                ],
            ],
        ], $context->getContext());

        $taxId = $this->getTaxId();
        $productId = $this->createProduct($templateId, $taxId, $context);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectCustomProductDoesntContainAllRequiredOptionsIfNoOptionsExist(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', Defaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            $context->getContext(),
            [
                'active' => true,
            ]
        );

        $productId = Uuid::randomHex();
        $taxId = $this->getTaxId();
        $this->createProduct($templateId, $taxId, $context);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectWithoutRequiredOptions(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', Defaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $this->templateRepository->create([
            [
                'id' => $templateId,
                'active' => true,
                'internalName' => self::TEMPLATE_NAME,
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'displayName' => self::TEMPLATE_NAME,
                    ],
                ],
                'options' => [
                    [
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'none-required-options',
                            ],
                        ],
                    ],
                ],
            ],
        ], $context->getContext());

        $taxId = $this->getTaxId();
        $productId = $this->createProduct($templateId, $taxId, $context);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        static::assertCount(0, $cart->getErrors());
    }

    public function testCollectWithoutMatchingRequiredOptionCount(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', Defaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            $context->getContext(),
            [
                'active' => true,
                'options' => [
                    [
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'required-option',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $productId = Uuid::randomHex();
        $taxId = $this->getTaxId();
        $this->createProduct($templateId, $taxId, $context);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectWithoutRequiredOption(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', Defaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $noneRequiredOptionId = Uuid::randomHex();
        $this->templateRepository->create([
            [
                'id' => $templateId,
                'active' => true,
                'internalName' => self::TEMPLATE_NAME,
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'displayName' => self::TEMPLATE_NAME,
                    ],
                ],
                'options' => [
                    [
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'required-options',
                            ],
                        ],
                    ],
                    [
                        'id' => $noneRequiredOptionId,
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'none-required-options',
                            ],
                        ],
                    ],
                ],
            ],
        ], $context->getContext());

        $productId = Uuid::randomHex();
        $taxId = $this->getTaxId();
        $this->createProduct($templateId, $taxId, $context);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
            $noneRequiredOptionId
        ));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectWithRequiredOption(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', Defaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $requiredOptionId = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            $context->getContext(),
            [
                'active' => true,
                'options' => [
                    [
                        'id' => $requiredOptionId,
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'required-options',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $taxId = $this->getTaxId();
        $productId = $this->createProduct($templateId, $taxId, $context);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));
        $templateLineItem->addChild((new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
            $requiredOptionId
        ))->setPayloadValue('value', 'on'));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(0, $errorCollection);
    }

    public function testCollectNoneExistingOptionReferenceId(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', Defaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $requiredOptionId = Uuid::randomHex();
        $this->templateRepository->create([
            [
                'id' => $templateId,
                'active' => true,
                'internalName' => self::TEMPLATE_NAME,
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'displayName' => self::TEMPLATE_NAME,
                    ],
                ],
                'options' => [
                    [
                        'id' => $requiredOptionId,
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'required-options',
                            ],
                        ],
                    ],
                ],
            ],
        ], $context->getContext());

        $productId = Uuid::randomHex();
        $taxId = $this->getTaxId();
        $this->createProduct($templateId, $taxId, $context);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->setRemovable(true);
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));
        $templateLineItem->addChild((new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
            Uuid::randomHex()
        ))->setPayloadValue('value', 'on'));

        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectWithOptionMissingPayloadValue(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', Defaults::SALES_CHANNEL);

        $templateId = Uuid::randomHex();
        $requiredOptionId = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            $context->getContext(),
            [
                'active' => true,
                'options' => [
                    [
                        'id' => $requiredOptionId,
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'required-options',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $productId = Uuid::randomHex();
        $taxId = $this->getTaxId();
        $this->createProduct($templateId, $taxId, $context);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
            $requiredOptionId
        ));
        $templateLineItem->setRemovable(true);
        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(1, $errorCollection);
        static::assertInstanceOf(SwagCustomizedProductsNotAvailableError::class, $errorCollection->first());
    }

    public function testCollectWithOptionValues(): void
    {
        $cart = $this->cartService->createNew('test-token');
        $context = $this->salesChannelContextFactory->create('test-saleschannel-token', Defaults::SALES_CHANNEL);

        $templateId = Uuid::randomHex();
        $requiredOptionId = Uuid::randomHex();
        $optionValueId = Uuid::randomHex();
        $this->templateRepository->create([
            [
                'id' => $templateId,
                'active' => true,
                'internalName' => self::TEMPLATE_NAME,
                'translations' => [
                    Defaults::LANGUAGE_SYSTEM => [
                        'displayName' => self::TEMPLATE_NAME,
                    ],
                ],
                'options' => [
                    [
                        'id' => $requiredOptionId,
                        'type' => Checkbox::NAME,
                        'position' => 1,
                        'typeProperties' => [],
                        'translations' => [
                            Defaults::LANGUAGE_SYSTEM => [
                                'displayName' => 'required-options',
                            ],
                        ],
                        'values' => [
                            [
                                'id' => $optionValueId,
                                'position' => 1,
                                'translations' => [
                                    Defaults::LANGUAGE_SYSTEM => [
                                        'displayName' => 'required-option-value',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $context->getContext());

        $taxId = $this->getTaxId();
        $productId = $this->createProduct($templateId, $taxId, $context);

        $templateLineItem = new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId
        );
        $templateLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId
        ));
        $optionLineItem = new LineItem(Uuid::randomHex(), CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE, $requiredOptionId);

        $optionLineItem->addChild(new LineItem(
            Uuid::randomHex(),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE,
            $optionValueId
        ));

        $templateLineItem->addChild($optionLineItem);
        $templateLineItem->setRemovable(true);
        $cart->add($templateLineItem);

        $this->cartDataCollector->collect(
            new CartDataCollection(),
            $cart,
            $context,
            new CartBehavior()
        );

        $errorCollection = $cart->getErrors();
        static::assertCount(0, $errorCollection);
        $optionValues = $cart->getLineItems()->filterFlatByType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE);
        static::assertCount(1, $optionValues);
        $optionValue = $optionValues[0];
        static::assertInstanceOf(LineItem::class, $optionValue);
    }

    private function getTaxId(): string
    {
        /** @var EntityRepositoryInterface $taxRepo */
        $taxRepo = $this->getContainer()->get(TaxDefinition::ENTITY_NAME . self::REPOSITORY_POSTFIX);
        $taxId = $taxRepo->searchIds(new Criteria(), Context::createDefaultContext())->firstId();

        static::assertNotNull($taxId);

        return $taxId;
    }

    private function createProduct(string $templateId, string $taxId, SalesChannelContext $context): string
    {
        $productId = Uuid::randomHex();

        /** @var EntityRepositoryInterface $productRepository */
        $productRepository = $this->getContainer()->get(ProductDefinition::ENTITY_NAME . self::REPOSITORY_POSTFIX);
        $productRepository->create([
            [
                'id' => $productId,
                'productNumber' => 'random-product-number',
                'stock' => 1,
                'name' => 'tolles test produkt',
                'price' => [
                    [
                        'currencyId' => Defaults::CURRENCY,
                        'gross' => 10 + (10 * 19 / 100),
                        'net' => 10,
                        'linked' => false,
                    ],
                ],
                'manufacturer' => ['name' => 'create'],
                'taxId' => $taxId,
                'active' => true,
                'swagCustomizedProductsTemplateId' => $templateId,
                'visibilities' => [
                    [
                        'productId' => $productId,
                        'salesChannelId' => $context->getSalesChannel()->getId(),
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ],
            ],
        ], $context->getContext());

        return $productId;
    }
}
