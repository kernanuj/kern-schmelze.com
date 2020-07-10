<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\TextField;
use Swag\CustomizedProducts\Template\TemplateDefinition;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class TemplateVersioningTest extends TestCase
{
    use ServicesTrait;

    private const TEMPLATE_INTERNAL_NAME = 'internalTemplateName';
    private const TEMPLATE_DISPLAY_NAME = 'Nice template display name';
    private const OPTION_DISPLAY_NAME = 'Nice option display name';
    private const TEST_PRODUCT_NAME = 'Test name of a product';
    private const TEST_PRODUCT_VARIANT_STOCK = 123;
    private const VALUE_DISPLAY_NAME = 'Nice value display name';
    private const REPOSITORY_SUFFIX = '.repository';

    /**
     * @var EntityRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $productRepository;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->templateRepository = $container->get(TemplateDefinition::ENTITY_NAME . self::REPOSITORY_SUFFIX);
        $this->optionRepository = $container->get(TemplateOptionDefinition::ENTITY_NAME . self::REPOSITORY_SUFFIX);
        $this->productRepository = $container->get(ProductDefinition::ENTITY_NAME . self::REPOSITORY_SUFFIX);
    }

    public function testCreateTemplate(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();

        $optionData = [
            'id' => $optionId,
            'templateId' => $templateId,
            'displayName' => self::OPTION_DISPLAY_NAME,
            'type' => TextField::NAME,
            'position' => 0,
            'typeProperties' => [],
        ];

        $context = Context::createDefaultContext();
        $this->createTemplate(
            $templateId,
            $context
        );

        $versionId = $this->templateRepository->createVersion($templateId, $context);
        $versionContext = $context->createWithVersionId($versionId);

        $this->optionRepository->create([$optionData], $versionContext);

        $this->templateRepository->merge($versionId, $versionContext);
    }

    public function testAddProductToTemplate(): void
    {
        $templateId = Uuid::randomHex();
        $context = Context::createDefaultContext();

        $this->createTemplate(
            $templateId,
            $context
        );

        $productId = $this->createProduct($context);

        $versionId = $this->templateRepository->createVersion($templateId, $context);
        $versionContext = $context->createWithVersionId($versionId);

        $this->productRepository->createVersion($productId, $versionContext, null, $versionContext->getVersionId());

        $this->templateRepository->update([
            [
                'id' => $templateId,
                'products' => [
                    ['id' => $productId],
                ],
            ],
        ], $versionContext);

        $this->templateRepository->merge($versionId, $versionContext);
    }

    public function testThatTemplateDoesVersionizeOption(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $context = Context::createDefaultContext();

        $this->createTemplate(
            $templateId,
            $context,
            [
                'options' => [
                    [
                        'id' => $optionId,
                        'templateId' => $templateId,
                        'displayName' => self::OPTION_DISPLAY_NAME,
                        'type' => TextField::NAME,
                        'position' => 0,
                        'typeProperties' => [],
                    ],
                ],
            ]
        );

        $versionId = $this->templateRepository->createVersion($templateId, $context);
        $context->createWithVersionId($versionId);

        $query = <<<SQL
SELECT `id` FROM `swag_customized_products_template_option`;
SQL;

        $optionIds = $this->getContainer()->get(Connection::class)->executeQuery($query)->fetchAll(FetchMode::COLUMN);

        static::assertNotNull($optionIds);
        static::assertCount(2, $optionIds);
    }

    public function testThatTemplateDoesVersionizeOptionSubEntities(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $context = Context::createDefaultContext();

        $this->createTemplate(
            $templateId,
            $context,
            [
                'options' => [
                    [
                        'id' => $optionId,
                        'templateId' => $templateId,
                        'displayName' => self::OPTION_DISPLAY_NAME,
                        'type' => TextField::NAME,
                        'position' => 0,
                        'typeProperties' => [],
                        'prices' => [
                            [
                                'id' => Uuid::randomHex(),
                                'currencyId' => Defaults::CURRENCY,
                            ],
                        ],
                        'values' => [
                            [
                                'id' => Uuid::randomHex(),
                                'displayName' => self::OPTION_DISPLAY_NAME,
                                'position' => 0,
                                'prices' => [
                                    [
                                        'id' => Uuid::randomHex(),
                                        'currencyId' => Defaults::CURRENCY,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->templateRepository->createVersion($templateId, $context);

        $optionPriceIds = $this->getContainer()->get(Connection::class)->fetchAll('SELECT `id` FROM `swag_customized_products_template_option_price`');
        $optionValueIds = $this->getContainer()->get(Connection::class)->fetchAll('SELECT `id` FROM `swag_customized_products_template_option_value`');
        $optionValuePriceIds = $this->getContainer()->get(Connection::class)->fetchAll('SELECT `id` FROM `swag_customized_products_template_option_value_price`');

        static::assertNotNull($optionPriceIds);
        static::assertNotNull($optionValueIds);
        static::assertNotNull($optionValuePriceIds);
        static::assertCount(2, $optionPriceIds);
        static::assertCount(2, $optionValueIds);
        static::assertCount(2, $optionValuePriceIds);
    }

    public function testThatTemplateDoesntVersionizeProducts(): void
    {
        static::markTestSkipped('Currently skipped, core needs to be fixed first');
        $templateId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $productId = $this->createProduct($context);

        $this->createTemplate(
            $templateId,
            $context,
            [
                'products' => [
                    ['id' => $productId],
                ],
            ]
        );

        $versionId = $this->templateRepository->createVersion($templateId, $context);
        $context->createWithVersionId($versionId);

        $query = <<<SQL
SELECT `id` FROM `product`;
SQL;

        $productIds = $this->getContainer()->get(Connection::class)->executeQuery($query)->fetchAll(FetchMode::COLUMN);

        static::assertNotNull($productIds);
        static::assertCount(2, $productIds);
    }

    public function testThatTemplateDoesntDeleteProductAssociations(): void
    {
        static::markTestSkipped('Currently skipped, core needs to be fixed first');
        $templateId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $productId = $this->createProduct($context);

        $this->createTemplate(
            $templateId,
            $context,
            [
                'products' => [
                    ['id' => $productId],
                ],
            ]
        );

        $versionContext = $context->createWithVersionId($this->templateRepository->createVersion($templateId, $context));
        $this->templateRepository->update([
            [
                'id' => $templateId,
                'products' => [
                    $productId => [
                        Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN => null,
                    ],
                ],
            ],
        ], $versionContext);

        $query = <<<SQL
SELECT `swag_customized_products_template_id` FROM `product`;
SQL;

        $id = $this->getContainer()->get(Connection::class)->executeQuery($query)->fetchAll(FetchMode::COLUMN)[0];

        static::assertNull($id);
    }

    private function createProduct(Context $context): string
    {
        $productId = Uuid::randomHex();

        $productNumber = Uuid::randomHex();
        $data = [
            'id' => $productId,
            'productNumber' => $productNumber,
            'stock' => 1,
            'name' => 'tolles test produkt',
            'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 10 + (10 * 19 / 100), 'net' => 10, 'linked' => false]],
            'manufacturer' => ['name' => 'create'],
            'tax' => ['name' => 'create', 'taxRate' => 19],
            'active' => true,
        ];
        $this->productRepository->create([$data], $context);

        return $productId;
    }
}
