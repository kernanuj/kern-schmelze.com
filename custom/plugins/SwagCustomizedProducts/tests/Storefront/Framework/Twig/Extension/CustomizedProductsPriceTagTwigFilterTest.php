<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Storefront\Framework\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\Seo\StorefrontSalesChannelTestHelper;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\SalesChannelRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Storefront\Framework\Twig\Extension\CustomizedProductsPriceTagTwigFilter;
use Symfony\Component\HttpFoundation\Request;

class CustomizedProductsPriceTagTwigFilterTest extends TestCase
{
    use IntegrationTestBehaviour;
    use StorefrontSalesChannelTestHelper;

    /**
     * @var SalesChannelContext
     */
    private $salesChannelContext;

    /**
     * @var LineItem
     */
    private $lineItem;

    /**
     * @var array
     */
    private $twigMockContext;

    /**
     * @var CustomizedProductsPriceTagTwigFilter
     */
    private $filter;

    protected function setUp(): void
    {
        /** @var Translator $translator */
        $translator = $this->getContainer()->get(Translator::class);
        $translator->resetInMemoryCache();
        $this->setContexts();
        $this->setLineItem();
        $this->setRequest();
        $this->setPriceTagFilter();
    }

    /**
     * @dataProvider dataProviderTestPriceTagDefault
     */
    public function testPriceTagsDefault(array $input, ?string $expected): void
    {
        $this->setSurcharge($input['surcharge'], $input['isOneTimeSurcharge']);
        $actual = $this->filter->generatePriceTag($this->twigMockContext, $this->lineItem);

        static::assertSame($expected, $actual);
    }

    /**
     * @dataProvider dataProviderTestQuantityPriceTags
     */
    public function testQuantityPriceTags(array $input, ?string $expected): void
    {
        $this->setSurcharge($input['surcharge'], $input['isOneTimeSurcharge'], $input['quantity']);
        $actual = $this->filter->generatePriceTag($this->twigMockContext, $this->lineItem);

        static::assertSame($expected, $actual);
    }

    public function testPriceTagsWithoutIsOneTimeSurchargePayload(): void
    {
        $input = ['surcharge' => 15.0, 'isOneTimeSurcharge' => false, 'quantity' => 10];
        $expected = '(+€17.85* per piece)';

        $this->setSurcharge($input['surcharge'], $input['isOneTimeSurcharge'], $input['quantity']);
        $this->lineItem->removePayloadValue('isOneTimeSurcharge');
        $actual = $this->filter->generatePriceTag($this->twigMockContext, $this->lineItem);

        static::assertSame($expected, $actual);
    }

    public function dataProviderTestQuantityPriceTags(): array
    {
        return [
            [
                ['surcharge' => 15.0, 'isOneTimeSurcharge' => false, 'quantity' => 10],
                '(+€17.85* per piece)',
            ], [
                ['surcharge' => 12.3456789, 'isOneTimeSurcharge' => false, 'quantity' => 3],
                '(+€14.69* per piece)',
            ], [
                ['surcharge' => 12.89, 'isOneTimeSurcharge' => false, 'quantity' => 1],
                '(+€15.34* per piece)',
            ], [
                ['surcharge' => 12.89, 'isOneTimeSurcharge' => true, 'quantity' => 10],
                '(+€15.34* once)',
            ], [
                ['surcharge' => 12.89, 'isOneTimeSurcharge' => true, 'quantity' => 1],
                '(+€15.34* once)',
            ],
        ];
    }

    public function dataProviderTestPriceTagDefault(): array
    {
        return [
            [
                ['surcharge' => 15.0, 'isOneTimeSurcharge' => false],
                '(+€17.85* per piece)',
            ], [
                ['surcharge' => 12.3456789, 'isOneTimeSurcharge' => false],
                '(+€14.69* per piece)',
            ], [
                ['surcharge' => 12.89, 'isOneTimeSurcharge' => false],
                '(+€15.34* per piece)',
            ], [
                ['surcharge' => 0.0, 'isOneTimeSurcharge' => false],
                null,
            ], [
                ['surcharge' => -15.0, 'isOneTimeSurcharge' => false],
                '(-€17.85* per piece)',
            ], [
                ['surcharge' => -12.3456789, 'isOneTimeSurcharge' => false],
                '(-€14.69* per piece)',
            ], [
                ['surcharge' => -12.89, 'isOneTimeSurcharge' => false],
                '(-€15.34* per piece)',
            ], [
                ['surcharge' => 15.0, 'isOneTimeSurcharge' => true],
                '(+€17.85* once)',
            ], [
                ['surcharge' => 12.3456789, 'isOneTimeSurcharge' => true],
                '(+€14.69* once)',
            ], [
                ['surcharge' => 12.89, 'isOneTimeSurcharge' => true],
                '(+€15.34* once)',
            ], [
                ['surcharge' => 0.0, 'isOneTimeSurcharge' => true],
                null,
            ], [
                ['surcharge' => -15.0, 'isOneTimeSurcharge' => true],
                '(-€17.85* once)',
            ], [
                ['surcharge' => -12.3456789, 'isOneTimeSurcharge' => true],
                '(-€14.69* once)',
            ], [
                ['surcharge' => -12.89, 'isOneTimeSurcharge' => true],
                '(-€15.34* once)',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestPriceTagDe
     */
    public function testPriceTagsDe(array $input, ?string $expected): void
    {
        $this->setRequest('de-DE');
        $this->setSurcharge($input['surcharge'], $input['isOneTimeSurcharge']);
        $actual = $this->filter->generatePriceTag($this->twigMockContext, $this->lineItem);

        static::assertSame($expected, $actual);
    }

    public function dataProviderTestPriceTagDe(): array
    {
        return [
            [
                ['surcharge' => 15.0, 'isOneTimeSurcharge' => false],
                '(+€17.85* pro Stück)',
            ], [
                ['surcharge' => 12.89, 'isOneTimeSurcharge' => false],
                '(+€15.34* pro Stück)',
            ], [
                ['surcharge' => 0.0, 'isOneTimeSurcharge' => false],
                null,
            ], [
                ['surcharge' => -15.0, 'isOneTimeSurcharge' => false],
                '(-€17.85* pro Stück)',
            ], [
                ['surcharge' => -12.89, 'isOneTimeSurcharge' => false],
                '(-€15.34* pro Stück)',
            ], [
                ['surcharge' => 15.0, 'isOneTimeSurcharge' => true],
                '(+€17.85* einmalig)',
            ], [
                ['surcharge' => 12.89, 'isOneTimeSurcharge' => true],
                '(+€15.34* einmalig)',
            ], [
                ['surcharge' => 0.0, 'isOneTimeSurcharge' => true],
                null,
            ], [
                ['surcharge' => -15.0, 'isOneTimeSurcharge' => true],
                '(-€17.85* einmalig)',
            ], [
                ['surcharge' => -12.89, 'isOneTimeSurcharge' => true],
                '(-€15.34* einmalig)',
            ],
        ];
    }

    private function setSurcharge(float $surchargeValue, bool $isOneTimeSurcharge, int $quantity = 1): void
    {
        /** @var QuantityPriceCalculator $calculator */
        $calculator = $this->getContainer()->get(QuantityPriceCalculator::class);
        $highTaxRules = new TaxRuleCollection([new TaxRule(19)]);
        $definition = new QuantityPriceDefinition($surchargeValue, $highTaxRules, 2, $isOneTimeSurcharge ? 1 : $quantity);

        $this->lineItem->setStackable(true);
        $this->lineItem->setQuantity($quantity);
        $this->lineItem->setPriceDefinition($definition);
        $this->lineItem->setPayload(['isOneTimeSurcharge' => $isOneTimeSurcharge]);
        $this->lineItem->setPrice($calculator->calculate(
            $definition,
            $this->salesChannelContext
        ));
    }

    private function setContexts(): void
    {
        $this->salesChannelContext = $this->createStorefrontSalesChannelContext(Uuid::randomHex(), 'random test storefront');
        $this->twigMockContext = ['context' => $this->salesChannelContext];
    }

    private function setLineItem(): void
    {
        $productId = Uuid::randomHex();

        /** @var EntityRepositoryInterface $productRepo */
        $productRepo = $this->getContainer()->get('product.repository');
        $productRepo->create([
            [
                'id' => $productId,
                'name' => 'random productName',
                'manufacturer' => [
                    'id' => Uuid::randomHex(),
                    'name' => 'amazing brand',
                ],
                'productNumber' => 'P1234',
                'tax' => [
                    'id' => Uuid::randomHex(),
                    'taxRate' => 19,
                    'name' => 'tax',
                ],
                'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 10, 'net' => 9, 'linked' => false]],
                'stock' => 10,
            ],
        ], $this->salesChannelContext->getContext());

        $lineItemUuid = Uuid::randomHex();
        $this->lineItem = new LineItem(
            $lineItemUuid,
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $productId
        );
        $this->lineItem->setRemovable(true);
    }

    private function setRequest(string $locale = 'en-GB'): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('iso', $locale));

        /** @var EntityRepositoryInterface $snippetSetRepository */
        $snippetSetRepository = $this->getContainer()->get('snippet_set.repository');
        $snippetSetId = $snippetSetRepository
            ->search($criteria, $this->salesChannelContext->getContext())
            ->first()
            ->getId();

        $requestStack = $this->getContainer()->get('request_stack');

        $request = new Request();
        $request->attributes->add([
            SalesChannelRequest::ATTRIBUTE_DOMAIN_SNIPPET_SET_ID => $snippetSetId,
        ]);

        $requestStack->push($request);
    }

    private function setPriceTagFilter(): void
    {
        $this->filter = $this->getContainer()->get(CustomizedProductsPriceTagTwigFilter::class);
    }
}
