<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\AdminFunctionalTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ColorPicker;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ColorSelect;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\DateTime;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\FileUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\HtmlEditor;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageSelect;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\NumberField;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Select;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Textarea;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\TextField;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Timestamp;
use Swag\CustomizedProducts\Template\TemplateEntity;
use Symfony\Component\HttpFoundation\Response;

class TemplateApiTest extends TestCase
{
    use AdminFunctionalTestBehaviour;

    private const TEMPLATE_INTERNAL_NAME = 'internalTemplateName';
    private const TEMPLATE_DISPLAY_NAME = 'Nice template display name';
    private const OPTION_DISPLAY_NAME = 'Nice option display name';
    private const TEST_PRODUCT_NAME = 'Test name of a product';
    private const TEST_PRODUCT_VARIANT_STOCK = 123;
    private const VALUE_DISPLAY_NAME = 'Nice value display name';

    public function testCreateTemplate(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $productId = Uuid::randomHex();

        $templateData = [
            'id' => $templateId,
            'internalName' => self::TEMPLATE_INTERNAL_NAME,
            'displayName' => self::TEMPLATE_DISPLAY_NAME,
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
                    'id' => $productId,
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
            ],
        ];

        $template = $this->createTemplate($templateData, $templateId);

        $options = $template->getOptions();
        static::assertNotNull($options);
        $firstOption = $options->first();
        static::assertNotNull($firstOption);
        static::assertSame($optionId, $firstOption->getId());
        static::assertSame(self::OPTION_DISPLAY_NAME, $firstOption->getDisplayName());

        $products = $template->getProducts();
        static::assertNotNull($products);
        $this->assertNormalProduct($products, $productId, $templateId);
    }

    public function testCreateTemplateWithVariantProducts(): void
    {
        $templateId = Uuid::randomHex();
        $productId = Uuid::randomHex();

        $templateData = [
            'id' => $templateId,
            'internalName' => self::TEMPLATE_INTERNAL_NAME,
            'displayName' => self::TEMPLATE_DISPLAY_NAME,
            'options' => [
                [
                    'id' => Uuid::randomHex(),
                    'displayName' => self::OPTION_DISPLAY_NAME,
                    'type' => TextField::NAME,
                    'position' => 0,
                    'typeProperties' => [],
                ],
            ],
            'products' => [
                [
                    'id' => $productId,
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
                [
                    'parentId' => $productId,
                    'stock' => self::TEST_PRODUCT_VARIANT_STOCK,
                    'productNumber' => 'P1234.1',
                ],
            ],
        ];

        $products = $this->createTemplate($templateData, $templateId)->getProducts();
        static::assertNotNull($products);
        static::assertCount(2, $products);

        foreach ($products as $product) {
            static::assertSame($templateId, $product->get('swagCustomizedProductsTemplateId'));

            if ($product->getId() === $productId) {
                static::assertSame(self::TEST_PRODUCT_NAME, $product->getName());
                static::assertSame(1, $product->getChildCount());
            } else {
                static::assertSame($productId, $product->getParentId());
                static::assertSame(self::TEST_PRODUCT_VARIANT_STOCK, $product->getStock());
            }
        }
    }

    public function testCreateTemplateWithOptionPrices(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();

        $templateData = [
            'id' => $templateId,
            'internalName' => self::TEMPLATE_INTERNAL_NAME,
            'displayName' => self::TEMPLATE_DISPLAY_NAME,
            'options' => [
                [
                    'id' => $optionId,
                    'displayName' => self::OPTION_DISPLAY_NAME,
                    'type' => TextField::NAME,
                    'position' => 0,
                    'typeProperties' => [],
                    'prices' => [
                        [
                            'currencyId' => Defaults::CURRENCY,
                            'price' => [
                                [
                                    'net' => 10,
                                    'currencyId' => Defaults::CURRENCY,
                                    'gross' => 11.9,
                                    'linked' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $options = $this->createTemplate($templateData, $templateId)->getOptions();
        static::assertNotNull($options);

        $firstOption = $options->first();
        static::assertNotNull($firstOption);
        static::assertSame($optionId, $firstOption->getId());

        $prices = $firstOption->getPrices();
        static::assertNotNull($prices);
        $firstPrice = $prices->first();
        static::assertNotNull($firstPrice);
        static::assertSame($optionId, $firstPrice->getTemplateOptionId());

        $priceCollection = $firstPrice->getPrice();
        static::assertNotNull($priceCollection);
        $price = $priceCollection->first();
        static::assertNotNull($price);
        static::assertSame(10.0, $price->getNet());
    }

    public function testCreateTemplateWithOptionValuePrices(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $valueId = Uuid::randomHex();

        $templateData = [
            'id' => $templateId,
            'internalName' => self::TEMPLATE_INTERNAL_NAME,
            'displayName' => self::TEMPLATE_DISPLAY_NAME,
            'options' => [
                [
                    'id' => $optionId,
                    'displayName' => self::OPTION_DISPLAY_NAME,
                    'type' => TextField::NAME,
                    'position' => 0,
                    'typeProperties' => [],
                    'values' => [
                        [
                            'id' => $valueId,
                            'position' => 0,
                            'value' => [
                                'test' => 123,
                                'foo' => 'bar',
                            ],
                            'displayName' => self::VALUE_DISPLAY_NAME,
                            'prices' => [
                                [
                                    'currencyId' => Defaults::CURRENCY,
                                    'price' => [
                                        [
                                            'net' => 10,
                                            'currencyId' => Defaults::CURRENCY,
                                            'gross' => 11.9,
                                            'linked' => true,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $options = $this->createTemplate($templateData, $templateId)->getOptions();
        static::assertNotNull($options);

        $firstOption = $options->first();
        static::assertNotNull($firstOption);
        static::assertSame($optionId, $firstOption->getId());

        $values = $firstOption->getValues();
        static::assertNotNull($values);

        $firstValue = $values->first();
        static::assertNotNull($firstValue);
        static::assertSame($optionId, $firstValue->getTemplateOptionId());
        static::assertSame(self::VALUE_DISPLAY_NAME, $firstValue->getDisplayName());

        $value = $firstValue->getValue();
        static::assertNotNull($value);
        static::assertCount(2, $value);
        static::assertSame('bar', $value['foo']);
        static::assertSame(123, $value['test']);

        $valuePrices = $firstValue->getPrices();
        static::assertNotNull($valuePrices);

        $firstPrice = $valuePrices->first();
        static::assertNotNull($firstPrice);
        static::assertSame($valueId, $firstPrice->getTemplateOptionValueId());

        $priceCollection = $firstPrice->getPrice();
        static::assertNotNull($priceCollection);
        $price = $priceCollection->first();
        static::assertNotNull($price);
        static::assertSame(10.0, $price->getNet());
    }

    public function testAddTemplateToProduct(): void
    {
        $productId = Uuid::randomHex();
        /** @var EntityRepositoryInterface $productRepo */
        $productRepo = $this->getContainer()->get('product.repository');
        $productRepo->create([
            [
                'id' => $productId,
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

        $templateId = Uuid::randomHex();
        $templateData = [
            'id' => $templateId,
            'internalName' => self::TEMPLATE_INTERNAL_NAME,
            'displayName' => self::TEMPLATE_DISPLAY_NAME,
            'options' => [
                [
                    'id' => Uuid::randomHex(),
                    'displayName' => self::OPTION_DISPLAY_NAME,
                    'type' => TextField::NAME,
                    'position' => 0,
                    'typeProperties' => [],
                ],
            ],
        ];

        $noProducts = $this->createTemplate($templateData, $templateId)->getProducts();
        static::assertNotNull($noProducts);
        static::assertCount(0, $noProducts);

        $productData = [
            'swagCustomizedProductsTemplateId' => $productId,
        ];
        $this->getBrowser()->request(
            'PATCH',
            '/api/v' . PlatformRequest::API_VERSION . '/swag-customized-products-template/' . $templateId . '/products/' . $productId,
            $productData
        );

        static::assertSame(
            Response::HTTP_NO_CONTENT,
            $this->getBrowser()->getResponse()->getStatusCode(),
            (string) $this->getBrowser()->getResponse()->getContent()
        );

        $products = $this->assertTemplate($templateId)->getProducts();
        static::assertNotNull($products);
        $this->assertNormalProduct($products, $productId, $templateId);
    }

    /**
     * @dataProvider createTemplateVariousTypesProvider
     */
    public function testCreateTemplateVariousTypes(string $type): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $productId = Uuid::randomHex();

        $templateData = [
            'id' => $templateId,
            'internalName' => self::TEMPLATE_INTERNAL_NAME,
            'displayName' => self::TEMPLATE_DISPLAY_NAME,
            'options' => [
                [
                    'id' => $optionId,
                    'displayName' => self::OPTION_DISPLAY_NAME,
                    'type' => $type,
                    'position' => 0,
                    'typeProperties' => [],
                ],
            ],
            'products' => [
                [
                    'id' => $productId,
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
            ],
        ];

        $template = $this->createTemplate($templateData, $templateId);

        $options = $template->getOptions();
        static::assertNotNull($options);
        $firstOption = $options->first();
        static::assertNotNull($firstOption);
        static::assertSame($optionId, $firstOption->getId());
        static::assertSame(self::OPTION_DISPLAY_NAME, $firstOption->getDisplayName());

        $products = $template->getProducts();
        static::assertNotNull($products);
        $this->assertNormalProduct($products, $productId, $templateId);
    }

    public function createTemplateVariousTypesProvider(): array
    {
        return [
            [
                Checkbox::NAME,
            ],
            [
                ColorPicker::NAME,
            ],
            [
                ColorSelect::NAME,
            ],
            [
                DateTime::NAME,
            ],
            [
                FileUpload::NAME,
            ],
            [
                HtmlEditor::NAME,
            ],
            [
                ImageSelect::NAME,
            ],
            [
                ImageUpload::NAME,
            ],
            [
                NumberField::NAME,
            ],
            [
                Select::NAME,
            ],
            [
                TextField::NAME,
            ],
            [
                Textarea::NAME,
            ],
            [
                Timestamp::NAME,
            ],
        ];
    }

    private function createTemplate(array $templateData, string $templateId): TemplateEntity
    {
        $this->getBrowser()->request(
            'POST',
            '/api/v' . PlatformRequest::API_VERSION . '/swag-customized-products-template',
            $templateData
        );
        static::assertSame(
            Response::HTTP_NO_CONTENT,
            $this->getBrowser()->getResponse()->getStatusCode(),
            (string) $this->getBrowser()->getResponse()->getContent()
        );

        return $this->assertTemplate($templateId);
    }

    private function assertTemplate(string $templateId): TemplateEntity
    {
        /** @var EntityRepositoryInterface $templateRepo */
        $templateRepo = $this->getContainer()->get('swag_customized_products_template.repository');
        $criteria = (new Criteria())
            ->addAssociation('options.prices')
            ->addAssociation('options.values.prices')
            ->addAssociation('products');

        /** @var TemplateEntity|null $template */
        $template = $templateRepo->search($criteria, Context::createDefaultContext())->get($templateId);
        static::assertNotNull($template);
        static::assertSame(self::TEMPLATE_INTERNAL_NAME, $template->getInternalName());
        static::assertSame(self::TEMPLATE_DISPLAY_NAME, $template->getDisplayName());

        return $template;
    }

    private function assertNormalProduct(
        ProductCollection $products,
        string $productId,
        string $templateId
    ): void {
        $firstProduct = $products->first();
        static::assertNotNull($firstProduct);
        static::assertSame($productId, $firstProduct->getId());
        static::assertSame(self::TEST_PRODUCT_NAME, $firstProduct->getName());
        static::assertSame($templateId, $firstProduct->get('swagCustomizedProductsTemplateId'));
    }
}
