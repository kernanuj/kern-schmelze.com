<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ColorSelect;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class ModifyTemplateOptionValuePriceConstraintTest extends TestCase
{
    use ServicesTrait;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EntityRepository
     */
    private $templateRepo;

    /**
     * @var EntityRepository
     */
    private $optionValuePriceRepo;

    protected function setUp(): void
    {
        $this->connection = $this->getContainer()->get(Connection::class);
        $this->templateRepo = $this->getContainer()->get('swag_customized_products_template.repository');
        $this->optionValuePriceRepo = $this->getContainer()->get('swag_customized_products_template_option_value_price.repository');
    }

    public function testNewConstraint(): void
    {
        $context = Context::createDefaultContext();
        $templateId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $valueId = Uuid::randomHex();

        $this->createTemplate(
            $templateId,
            $context,
            $this->getColorSelectTemplateData(
                $templateId,
                $productId,
                $optionId,
                $valueId,
                '#ffffff'
            )
        );

        $this->optionValuePriceRepo->create(
            [
                [
                    'templateOptionValueId' => $valueId,
                    'percentageSurcharge' => '10.0',
                ],
            ],
            $context
        );

        $result = $this->optionValuePriceRepo->create(
            [
                [
                    'templateOptionValueId' => $valueId,
                    'percentageSurcharge' => '20.0',
                ],
            ],
            $context
        );

        static::assertNotNull($result);
    }

    public function testNewConstraintFailed(): void
    {
        $context = Context::createDefaultContext();
        $templateId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $valueId = Uuid::randomHex();
        $ruleId = Uuid::randomHex();

        $this->templateRepo->create(
            [
                $this->getColorSelectTemplateData(
                    $templateId,
                    $productId,
                    $optionId,
                    $valueId,
                    '#ffffff'
                ),
            ],
            $context
        );

        $this->optionValuePriceRepo->create(
            [
                [
                    'templateOptionValueId' => $valueId,
                    'percentageSurcharge' => '10.0',
                    'rule' => [
                        'id' => $ruleId,
                        'name' => 'rule',
                        'priority' => 10,
                    ],
                ],
            ],
            $context
        );

        self::expectException(UniqueConstraintViolationException::class);

        $result = $this->optionValuePriceRepo->create(
            [
                [
                    'templateOptionValueId' => $valueId,
                    'percentageSurcharge' => '20.0',
                    'rule' => [
                        'id' => $ruleId,
                        'name' => 'rule',
                        'priority' => 20,
                    ],
                ],
            ],
            $context
        );
    }

    private function getColorSelectTemplateData(
        string $templateId,
        string $productId,
        string $optionId,
        string $valueId,
        string $value
    ): array {
        return [
            'id' => $templateId,
            'internalName' => 'internalName',
            'displayName' => 'displayName',
            'options' => [
                [
                    'id' => $optionId,
                    'displayName' => 'colorSelectOption',
                    'type' => ColorSelect::NAME,
                    'position' => 0,
                    'typeProperties' => [],
                    'values' => [
                        [
                            'id' => $valueId,
                            'displayName' => 'rot',
                            'value' => [
                                '_value' => $value,
                            ],
                            'position' => 1,
                        ],
                    ],
                ],
            ],
            'products' => [
                [
                    'id' => $productId,
                    'name' => 'Own Product',
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
    }
}
