<?php declare(strict_types=1);

namespace InvMixerProduct\Service\Checkout;

use InvMixerProduct\Constants;
use InvMixerProduct\Helper\LineItemAccessor;
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
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class MixProductCartProcessor implements CartProcessorInterface
{
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

    public function process(
        CartDataCollection $data,
        Cart $original,
        Cart $toCalculate,
        SalesChannelContext $salesChannelContext,
        CartBehavior $behavior
    ): void {

        $subjectContainerProductLineItems = $original->getLineItems()->filterType(
            Constants::LINE_ITEM_TYPE_IDENTIFIER
        );

        if ($subjectContainerProductLineItems->count() === 0) {
            return;
        }

        foreach ($subjectContainerProductLineItems as $subjectContainerProductLineItem) {
            if (true !== LineItemAccessor::isContainsMixContainerProduct($subjectContainerProductLineItem)) {
                continue;
            }
            $this->calculateChildProductPrices($subjectContainerProductLineItem, $salesChannelContext);

            $priceOfChildProducts = $subjectContainerProductLineItem->getChildren()->getPrices()->sum();

            $priceCollection = new PriceCollection($subjectContainerProductLineItem->getChildren()->getPrices());


            //duplicate price to set unit price to price of all child items for quantity of 1
            $finalPrice = $this->calculateActualPriceForContainerLineItem(
                $subjectContainerProductLineItem,
                $priceCollection
            );
            $subjectContainerProductLineItem->setPrice($finalPrice);

            $toCalculate->add($subjectContainerProductLineItem);
        }
    }

    /**
     * @param LineItem $bundleLineItem
     * @param SalesChannelContext $context
     */
    private function calculateChildProductPrices(LineItem $bundleLineItem, SalesChannelContext $context): void
    {
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
    }

    private function calculateActualPriceForContainerLineItem(
        LineItem $subjectLineItem,
        PriceCollection $priceCollection

    ): CalculatedPrice {

        $price = $priceCollection->sum();

        $newPrice = new CalculatedPrice(
            $price->getTotalPrice() / $subjectLineItem->getQuantity(),
            $price->getTotalPrice(),
            $price->getCalculatedTaxes(),
            $price->getTaxRules(),
            $price->getQuantity(),
            $price->getReferencePrice(),
            $price->getListPrice()
        );

        return $newPrice;
    }
}
