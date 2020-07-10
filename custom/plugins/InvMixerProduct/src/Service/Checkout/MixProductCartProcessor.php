<?php declare(strict_types=1);

namespace InvMixerProduct\Service\Checkout;

use InvMixerProduct\Constants;
use InvMixerProduct\Helper\LineItemAccessor;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\Exception\MissingLineItemPriceException;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsNotAvailableError;
use Swag\CustomizedProducts\Template\Exception\NoProductException;
use function array_pop;
use function count;
use function get_class;

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

            $subjectContainerProductLineItem->setPrice(
                $subjectContainerProductLineItem->getChildren()->getPrices()->sum()
            );

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

    /**
     * @return array<CalculatedPrice|null>
     */
    private function getPrices(LineItem $lineItem): array
    {
        $prices = [];

        foreach ($lineItem->getChildren() as $childLineItem) {
            if ($childLineItem->hasChildren()) {
                foreach ($this->getPrices($childLineItem) as $price) {
                    $prices[] = $price;
                }
            }

            if (!$childLineItem->getPrice() instanceof CalculatedPrice) {
                continue;
            }

            $prices[] = $childLineItem->getPrice();
        }

        return $prices;
    }

    private function calculateProduct(
        LineItemCollection $products,
        SalesChannelContext $context,
        string $templateId
    ): void {
        $customizedProduct = $products->first();
        if ($customizedProduct === null) {
            throw new NoProductException($templateId);
        }

        /** @var QuantityPriceDefinition|null $priceDefinition */
        $priceDefinition = $customizedProduct->getPriceDefinition();

        if ($priceDefinition === null) {
            throw new MissingLineItemPriceException($customizedProduct->getId());
        }

        $customizedProduct->setPrice(
            $this->quantityPriceCalculator->calculate($priceDefinition, $context)
        );
    }

    private function calculatePrices(
        LineItemCollection $optionLineItems,
        LineItemCollection $products,
        SalesChannelContext $context
    ): void {
        foreach ($optionLineItems as $optionLineItem) {
            if ($optionLineItem->hasChildren()) {
                $this->calculatePrices(
                    $optionLineItem->getChildren()->filterType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE),
                    $products,
                    $context
                );
            }

            $priceDefinition = $optionLineItem->getPriceDefinition();
            if ($priceDefinition === null) {
                continue;
            }

            switch (get_class($priceDefinition)) {
                case QuantityPriceDefinition::class:
                    $price = $this->quantityPriceCalculator->calculate($priceDefinition, $context);
                    break;
                case PercentagePriceDefinition::class:
                    $price = $this->percentagePriceCalculator->calculate(
                        $priceDefinition->getPercentage(),
                        $products->getPrices(),
                        $context
                    );
                    break;
                default:
                    throw new MissingLineItemPriceException($optionLineItem->getId());
            }

            $optionLineItem->setPrice($price);
        }
    }

    private function groupLineItemsByConfigurationHash(
        LineItemCollection $customizedProductsLineItems,
        Cart $toCalculate
    ): void {
        $hashLineItemMap = [];
        foreach ($customizedProductsLineItems as $lineItem) {
            if (!$lineItem->hasPayloadValue(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH)) {
                $customizedProductsLineItems->remove($lineItem->getId());
                $toCalculate->addErrors(new SwagCustomizedProductsNotAvailableError($lineItem->getId()));
                continue;
            }

            $key = $lineItem->getPayloadValue(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH);

            $hashLineItemMap[$key][] = $lineItem;
        }

        foreach ($hashLineItemMap as $lineItems) {
            if (count($lineItems) <= 1) {
                continue;
            }

            $firstLineItem = array_pop($lineItems);

            $finalQuantity = $firstLineItem->getQuantity();
            foreach ($lineItems as $lineItem) {
                $finalQuantity += $lineItem->getQuantity();
                $customizedProductsLineItems->remove($lineItem->getId());
            }

            $firstLineItem->setStackable(true);
            $firstLineItem->setQuantity($finalQuantity);
            $firstLineItem->setStackable(false);
        }
    }
}
