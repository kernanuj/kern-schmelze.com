<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout\Cart;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class CustomizedProductCartCalculationTest extends TestCase
{
    use ServicesTrait;

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
        $this->cartService = $container->get(CartService::class);
        $this->salesChannelContextFactory = $container->get(SalesChannelContextFactory::class);
    }

    public function testOneTimeRelativeSurchargesCalculation(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $this->createTemplate(
            $templateId,
            Context::createDefaultContext(),
            [
                'active' => true,
                'options' => [
                    [
                        'id' => $optionId,
                        'displayName' => 'Relative one time',
                        'type' => Checkbox::NAME,
                        'oneTimeSurcharge' => true,
                        'relativeSurcharge' => true,
                        'percentageSurcharge' => 10.0,
                        'position' => 1,
                        'typeProperties' => [],
                    ],
                ],
                'products' => [
                    [
                        'id' => $productId,
                        'name' => 'TestProduct',
                        'manufacturer' => [
                            'id' => Uuid::randomHex(),
                            'name' => 'amazing brand',
                        ],
                        'productNumber' => 'CP1234',
                        'tax' => ['id' => Uuid::randomHex(), 'taxRate' => 19, 'name' => 'tax'],
                        'price' => [
                            [
                                'currencyId' => Defaults::CURRENCY,
                                'gross' => 19.99,
                                'net' => 16.80,
                                'linked' => true,
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
        $token = Uuid::randomHex();
        $salesChannelContext = $this->salesChannelContextFactory->create($token, Defaults::SALES_CHANNEL);
        $cart = new Cart('testCart', $token);
        $customizedProductLineItem = $this->buildLineItem(3, $templateId, $productId, [$optionId]);
        $cart = $this->cartService->add($cart, $customizedProductLineItem, $salesChannelContext);

        /**
         * 3 * 19.99 = 59.97 (Product price)
         * 1 * 10% of 19.99 = 1.999 (One time relative surcharge)
         * Sum = 61.969, Rounded Sum = 61.97
         */
        static::assertSame(61.97, $cart->getPrice()->getTotalPrice());
    }

    private function buildLineItem(int $quantity, string $templateId, string $productId, array $optionIds): LineItem
    {
        $templateLineItem = new LineItem(
            $templateId,
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $templateId,
            $quantity
        );

        $productLineItem = new LineItem(
            $productId,
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $productId,
            $quantity
        );

        $templateLineItem->addChild($productLineItem);

        foreach ($optionIds as $optionId) {
            $optionLineItem = new LineItem(
                $optionId,
                CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE,
                $optionId
            );
            $optionLineItem->setPayloadValue('value', 'on');

            $templateLineItem->addChild($optionLineItem);
        }

        return $templateLineItem;
    }
}
