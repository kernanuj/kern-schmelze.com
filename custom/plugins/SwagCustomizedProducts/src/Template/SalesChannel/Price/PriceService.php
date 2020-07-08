<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\SalesChannel\Price;

use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPriceAbleEntity\TemplateOptionPriceAbleEntity;
use Swag\CustomizedProducts\Template\TemplateEntity;

final class PriceService
{
    /**
     * @var QuantityPriceCalculator
     */
    private $calculator;

    public function __construct(QuantityPriceCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public function calculateCurrencyPrices(
        TemplateEntity $template,
        SalesChannelContext $context
    ): void {
        $options = $template->getOptions();
        if ($options === null) {
            return;
        }

        foreach ($options as $option) {
            $this->calculateCurrencyPrice($option, $context);

            $optionValues = $option->getValues();
            if ($optionValues === null) {
                continue;
            }

            foreach ($optionValues as $optionValue) {
                $this->calculateCurrencyPrice($optionValue, $context);
            }
        }
    }

    private function calculateCurrencyPrice(
        TemplateOptionPriceAbleEntity $priceAbleEntity,
        SalesChannelContext $salesChannelContext
    ): void {
        $currency = $salesChannelContext->getCurrency();
        $currentCurrencyId = $currency->getId();
        $prices = $priceAbleEntity->getPrice();
        $taxId = $priceAbleEntity->getTaxId();

        // No absolute surcharges
        if ($prices === null || $taxId === null) {
            return;
        }

        /** @var Price $currencyPrice */
        $currencyPrice = $prices->getCurrencyPrice($currentCurrencyId);
        $taxRules = $salesChannelContext->buildTaxRules($taxId);

        // Manually set surcharges
        if ($currencyPrice->getCurrencyId() !== Defaults::CURRENCY) {
            $currencyPrice = $this->getTaxStatePrice($currencyPrice, $salesChannelContext);
            $this->setCalculatedPrice($priceAbleEntity, $currencyPrice, $taxRules, $currency, $salesChannelContext);

            return;
        }

        // Calculate via currency factor
        $basePriceValue = $this->getTaxStatePrice($currencyPrice, $salesChannelContext);
        $currencyPrice = $basePriceValue * $currency->getFactor();
        $this->setCalculatedPrice($priceAbleEntity, $currencyPrice, $taxRules, $currency, $salesChannelContext);
    }

    private function getTaxStatePrice(Price $basePrice, SalesChannelContext $salesChannelContext): float
    {
        if ($salesChannelContext->getTaxState() === CartPrice::TAX_STATE_GROSS) {
            return $basePrice->getGross();
        }

        return $basePrice->getNet();
    }

    private function setCalculatedPrice(
        TemplateOptionPriceAbleEntity $priceAbleEntity,
        float $currencyPrice,
        TaxRuleCollection $taxRules,
        CurrencyEntity $currency,
        SalesChannelContext $salesChannelContext
    ): void {
        $priceAbleEntity->setCalculatedPrice(
            $this->calculator->calculate(
                new QuantityPriceDefinition(
                    $currencyPrice,
                    $taxRules,
                    $currency->getDecimalPrecision(),
                    1,
                    true
                ),
                $salesChannelContext
            )
        );
    }
}
