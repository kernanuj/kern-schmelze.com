<?php declare(strict_types=1);

namespace InvMixerProduct\Service\Checkout;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class MixProductCartProcessor implements CartProcessorInterface
{

    use MixProductCartTrait;

    /**
     * @var QuantityPriceCalculator
     */
    private $quantityPriceCalculator;

    /**
     * @var PercentagePriceCalculator
     */
    private $percentagePriceCalculator;

    public function __construct(
        QuantityPriceCalculator $quantityPriceCalculator,
        PercentagePriceCalculator $percentagePriceCalculator
    ) {
        $this->quantityPriceCalculator = $quantityPriceCalculator;
        $this->percentagePriceCalculator = $percentagePriceCalculator;
    }

    /**
     * @param CartDataCollection $data
     * @param Cart $original
     * @param Cart $toCalculate
     * @param SalesChannelContext $salesChannelContext
     * @param CartBehavior $behavior
     */
    public function process(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $salesChannelContext,
        CartBehavior $behavior
    ): void {

        if (true !== $this->isCartContainsSubjectLineItems($original)) {
            return;
        }

        $subjectContainerProductLineItems = $this->getSubjectLineItemsFromCart($original);

        foreach ($subjectContainerProductLineItems as $subjectContainerProductLineItem) {
            $this->calculatePriceForContainerLineItem($subjectContainerProductLineItem, $salesChannelContext);
            $toCalculate->add($subjectContainerProductLineItem);
        }
    }

    /**
     * @param LineItem $subjectContainerProductLineItem
     * @param SalesChannelContext $salesChannelContext
     */
    private function calculatePriceForContainerLineItem(
        LineItem $subjectContainerProductLineItem,
        SalesChannelContext $salesChannelContext
    ): void {
        $this->calculateChildProductPrices($subjectContainerProductLineItem, $salesChannelContext);

        $priceCollection = new PriceCollection($subjectContainerProductLineItem->getChildren()->getPrices());

        //duplicate price to set unit price to price of all child items for quantity of 1
        $finalPrice = $this->calculateActualPriceForContainerLineItem(
            $subjectContainerProductLineItem,
            $priceCollection,
            $salesChannelContext
        );
        $subjectContainerProductLineItem->setPrice($finalPrice);
    }

    /**
     * @param LineItem $bundleLineItem
     * @param SalesChannelContext $context
     */
    private function calculateChildProductPrices(LineItem $bundleLineItem, SalesChannelContext $context): void
    {
        $products = $bundleLineItem->getChildren()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE);

        foreach ($products as $product) {
            /** @var QuantityPriceDefinition $priceDefinition */
            $priceDefinition = $product->getPriceDefinition();

            $product->setPrice(
                $this->quantityPriceCalculator->calculate($priceDefinition, $context)
            );
        }
    }

    /**
     * @param LineItem $subjectLineItem
     * @param PriceCollection $priceCollection
     * @param SalesChannelContext $salesChannelContext
     * @return CalculatedPrice
     */
    private function calculateActualPriceForContainerLineItem(
        LineItem $subjectLineItem,
        PriceCollection $priceCollection,
        SalesChannelContext $salesChannelContext

    ): CalculatedPrice {

        $price = $priceCollection->sum();

        //fix issue where mollie payments uses tax calucaltion on whole order, not on order items. therefore a rounding error occurs.
        // so we have to set the tax based on the sum, not on the individual line items which already have the tax calculated
        $calculatedPrice = $this->quantityPriceCalculator->calculate(
            new QuantityPriceDefinition(
                $price->getTotalPrice(),
                $price->getTaxRules(),
                $salesChannelContext->getCurrency()->getDecimalPrecision(),
                1,
                true
            ),
            $salesChannelContext
        );


        // this would sum up the wrong taxes since they are already rounded
        return new CalculatedPrice(
            $price->getTotalPrice() / $subjectLineItem->getQuantity(),
            $price->getTotalPrice(),
            $calculatedPrice->getCalculatedTaxes(),
            $price->getTaxRules(),
            $price->getQuantity(),
            $price->getReferencePrice(),
            $price->getListPrice()
        );

    }
}
