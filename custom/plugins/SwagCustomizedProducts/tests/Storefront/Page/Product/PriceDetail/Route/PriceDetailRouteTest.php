<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Storefront\Page\Product\PriceDetail\Route;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Swag\CustomizedProducts\Storefront\Controller\CustomizedProductsCartController;
use Swag\CustomizedProducts\Storefront\Page\Product\PriceDetail\Route\AbstractPriceDetailRoute;
use Swag\CustomizedProducts\Storefront\Page\Product\PriceDetail\Route\PriceDetailRoute;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Select;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Textarea;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\TextField;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;
use Symfony\Component\HttpFoundation\Request;

class PriceDetailRouteTest extends TestCase
{
    use ServicesTrait;

    private const NORMAL_OPTION_DISPLAYNAME = 'Normal option';
    private const ONE_TIME_SURCHARGES_OPTION = 'One time surcharges option';
    private const SELECT_OPTION_DISPLAYNAME = 'Example select option';
    private const SELECTION_1 = 'Selection 1';

    /**
     * @var AbstractPriceDetailRoute
     */
    private $priceDetailRoute;

    /**
     * @var SalesChannelContextFactory
     */
    private $salesChannelContextFactory;

    protected function setUp(): void
    {
        $container = $this->getContainer();
        $this->salesChannelContextFactory = $container->get(SalesChannelContextFactory::class);
        $this->priceDetailRoute = $container->get(PriceDetailRoute::class);
    }

    public function testPriceDetail(): void
    {
        $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), Defaults::SALES_CHANNEL);
        $templateId = Uuid::randomHex();
        $optionNormalId = Uuid::randomHex();
        $optionOneTimeId = Uuid::randomHex();
        $optionSelectId = Uuid::randomHex();
        $optionValueId = Uuid::randomHex();
        $productId = Uuid::randomHex();

        $taxId = $this->getValidTaxId();
        $this->createTemplate(
            $templateId,
            $salesChannelContext->getContext(),
            [
                'active' => true,
                'products' => [
                    [
                        'id' => $productId,
                        'name' => 'productName',
                        'manufacturer' => [
                            'id' => Uuid::randomHex(),
                            'name' => 'amazing brand',
                        ],
                        'productNumber' => 'CP1234',
                        'tax' => ['id' => $taxId, 'taxRate' => 19, 'name' => 'tax'],
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
                'options' => [
                    [
                        'id' => $optionNormalId,
                        'displayName' => self::NORMAL_OPTION_DISPLAYNAME,
                        'type' => TextField::NAME,
                        'position' => 0,
                        'taxId' => $taxId,
                        'typeProperties' => [
                            'minLength' => 100,
                            'maxLength' => 500,
                        ],
                        'price' => [
                            [
                                'currencyId' => Defaults::CURRENCY,
                                'net' => 10,
                                'gross' => 10,
                                'linked' => true,
                            ],
                        ],
                    ],
                    [
                        'id' => $optionOneTimeId,
                        'displayName' => self::ONE_TIME_SURCHARGES_OPTION,
                        'type' => Textarea::NAME,
                        'position' => 1,
                        'oneTimeSurcharge' => true,
                        'taxId' => $taxId,
                        'typeProperties' => [
                            'minLength' => 100,
                            'maxLength' => 500,
                        ],
                        'price' => [
                            [
                                'currencyId' => Defaults::CURRENCY,
                                'net' => 3.33,
                                'gross' => 3.33,
                                'linked' => true,
                            ],
                        ],
                    ],
                    [
                        'id' => $optionSelectId,
                        'displayName' => self::SELECT_OPTION_DISPLAYNAME,
                        'type' => Select::NAME,
                        'position' => 2,
                        'taxId' => $taxId,
                        'typeProperties' => [],
                        'price' => [
                            [
                                'currencyId' => Defaults::CURRENCY,
                                'net' => 5.50,
                                'gross' => 5.50,
                                'linked' => true,
                            ],
                        ],
                        'values' => [
                            [
                                'id' => $optionValueId,
                                'position' => 0,
                                'displayName' => self::SELECTION_1,
                                'isOneTimeSurcharge' => 'true',
                                'price' => [
                                    [
                                        'currencyId' => Defaults::CURRENCY,
                                        'net' => .5,
                                        'gross' => .5,
                                        'linked' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $request = $this->createRequest($templateId, $optionNormalId, $optionOneTimeId, [$optionSelectId, $optionValueId], $productId);

        $priceDetails = $this->priceDetailRoute->priceDetail($request, $salesChannelContext);
        static::assertSame(10.0, $priceDetails->getProductPrice());

        $surcharges = $priceDetails->getSurcharges();
        static::assertNotEmpty($surcharges);
        static::assertSame(
            [
                self::NORMAL_OPTION_DISPLAYNAME => 10.0,
                self::SELECT_OPTION_DISPLAYNAME => 5.5,
                self::SELECTION_1 => .5,
            ],
            $surcharges
        );
        static::assertSame(16.0, $priceDetails->getSurchargesSubTotal());

        $oneTimeSurcharges = $priceDetails->getOneTimeSurcharges();
        static::assertNotEmpty($oneTimeSurcharges);
        static::assertSame(
            [
                self::ONE_TIME_SURCHARGES_OPTION => 3.33,
            ],
            $oneTimeSurcharges
        );
        static::assertSame(3.33, $priceDetails->getOneTimeSurchargesSubTotal());

        static::assertSame(29.33, $priceDetails->getTotalPrice());
    }

    private function createRequest(
        string $templateId,
        string $optionNormalId,
        string $optionOneTimeId,
        array $optionWithOptionValues,
        string $productId
    ): Request {
        return new Request(
            [],
            [
                CustomizedProductsCartController::CUSTOMIZED_PRODUCTS_TEMPLATE_REQUEST_PARAMETER => [
                    'id' => $templateId,
                    'options' => [
                        $optionNormalId => [
                            'value' => 'example customer text',
                        ],
                        $optionOneTimeId => [
                            'value' => 'example customer text area',
                        ],
                        $optionWithOptionValues[0] => [
                            'value' => $optionWithOptionValues[1],
                        ],
                    ],
                ],
                'lineItems' => [
                    $productId => [
                        'id' => $productId,
                        'referencedId' => $productId,
                        'type' => LineItem::PRODUCT_LINE_ITEM_TYPE,
                        'quantity' => 1,
                        'stackable' => true,
                        'removable' => true,
                    ],
                ],
                'product-name' => 'test234',
                'brand-name' => 'team-services',
            ]
        );
    }
}
