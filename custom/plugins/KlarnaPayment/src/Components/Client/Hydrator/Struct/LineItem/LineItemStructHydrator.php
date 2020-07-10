<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\LineItem;

use KlarnaPayment\Components\Client\Hydrator\Struct\ProductIdentifier\ProductIdentifierStructHydratorInterface;
use KlarnaPayment\Components\Client\Struct\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItem as CartLineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection as CartLineItemCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Promotion\Cart\PromotionProcessor;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\Collection;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\Currency\CurrencyEntity;

class LineItemStructHydrator implements LineItemStructHydratorInterface
{
    /** @var ProductIdentifierStructHydratorInterface */
    private $productIdentifierHydrator;

    /** @var EntityRepositoryInterface */
    private $productRepository;

    public function __construct(
        ProductIdentifierStructHydratorInterface $productIdentifierHydrator,
        EntityRepositoryInterface $productRepository
    ) {
        $this->productIdentifierHydrator = $productIdentifierHydrator;
        $this->productRepository         = $productRepository;
    }

    public function hydrate(CartLineItemCollection $lineItems, CurrencyEntity $currency, Context $context): array
    {
        $products = $this->loadProducts($lineItems, $context);

        $result = [];

        foreach ($lineItems as $item) {
            $lineItem = new LineItem();
            $lineItem->assign([
                'type'      => $this->matchType($item->getType()),
                'reference' => $this->getReferenceNumber($item),
                'name'      => $item->getLabel(),
                'quantity'  => $item->getQuantity(),
            ]);

            if (null !== $item->getCover()) {
                $lineItem->assign([
                    'imageUrl' => $item->getCover()->getUrl(),
                ]);
            }

            if ($item->getType() === CartLineItem::PRODUCT_LINE_ITEM_TYPE) {
                /** @var null|ProductEntity $product */
                $product = $products->get((string) $item->getReferencedId());

                if (null !== $product) {
                    $lineItem->assign([
                        'productId'         => $product->getId(),
                        'quantityUnit'      => $this->getUnitNameFromProduct($product),
                        'productIdentifier' => $this->productIdentifierHydrator->hydrate($product),
                    ]);
                }
            }

            if (null !== $item->getPrice()) {
                $totalTaxAmount = $this->getTotalTaxAmount($item->getPrice()->getCalculatedTaxes());

                $totalAmount = $item->getPrice()->getTotalPrice();
                $unitPrice   = $item->getPrice()->getUnitPrice();

                if ($context->getTaxState() === CartPrice::TAX_STATE_NET) {
                    $totalAmount += $totalTaxAmount;
                    $unitPrice += round($totalTaxAmount / $item->getQuantity(), $currency->getDecimalPrecision());
                }

                $lineItem->assign([
                    'precision'      => $currency->getDecimalPrecision(),
                    'unitPrice'      => $unitPrice,
                    'totalAmount'    => $totalAmount,
                    'totalTaxAmount' => $totalTaxAmount,
                    'taxRate'        => $this->getTaxRate($item->getPrice()),
                ]);
            }

            $result[] = $lineItem;
        }

        return $result;
    }

    private function getTaxRate(CalculatedPrice $price): float
    {
        $taxRate = 0;

        foreach ($price->getCalculatedTaxes() as $tax) {
            if ($tax->getTaxRate() > $taxRate) {
                $taxRate = $tax->getTaxRate();
            }
        }

        return $taxRate;
    }

    private function getTotalTaxAmount(CalculatedTaxCollection $taxes): float
    {
        $totalTaxAmount = 0;

        foreach ($taxes as $tax) {
            $totalTaxAmount += $tax->getTax();
        }

        return $totalTaxAmount;
    }

    private function matchType(string $type): string
    {
        if ($type === CartLineItem::PRODUCT_LINE_ITEM_TYPE) {
            return LineItem::TYPE_PHYSICAL;
        }

        if ($type === CartLineItem::CREDIT_LINE_ITEM_TYPE) {
            return LineItem::TYPE_DISCOUNT;
        }

        if ($type === PromotionProcessor::LINE_ITEM_TYPE) {
            return LineItem::TYPE_DISCOUNT;
        }

        // TODO: Add surcharge as soon as Shopware supports it.

        return LineItem::TYPE_PHYSICAL;
    }

    private function loadProducts(Collection $lineItems, Context $context): EntityCollection
    {
        $products = $this->getReferenceIds($lineItems);

        if (empty($products)) {
            return new EntityCollection();
        }

        $criteria = new Criteria();
        $criteria->addAssociation('unit');
        $criteria->addAssociation('categories');
        $criteria->addAssociation('manufacturer');

        $products = $this->productRepository->search($criteria, $context);

        if (!$products->count()) {
            return new EntityCollection();
        }

        return $products->getEntities();
    }

    private function getReferenceIds(Collection $lineItems): array
    {
        return $lineItems->fmap(static function (Struct $lineItem) {
            if ($lineItem instanceof CartLineItem) {
                return $lineItem->getReferencedId();
            }

            if ($lineItem instanceof OrderLineItemEntity) {
                return $lineItem->getReferencedId();
            }

            return null;
        });
    }

    private function getUnitNameFromProduct(ProductEntity $product): ?string
    {
        if (null === $product->getUnit()) {
            return null;
        }

        return $product->getUnit()->getTranslation('shortCode');
    }

    private function getReferenceNumber(CartLineItem $cartLineItem): string
    {
        if ($cartLineItem->hasPayloadValue('productNumber')) {
            $referenceNumber = $cartLineItem->getPayloadValue('productNumber');
        } else {
            $referenceNumber = (string) $cartLineItem->getReferencedId();
        }

        return mb_strimwidth($referenceNumber, 0, 64);
    }
}
