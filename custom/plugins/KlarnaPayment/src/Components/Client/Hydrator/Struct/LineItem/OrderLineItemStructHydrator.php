<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\LineItem;

use KlarnaPayment\Components\Client\Struct\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItem as CartLineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItem as ShopwareLineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Promotion\Cart\PromotionProcessor;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Currency\CurrencyEntity;

class OrderLineItemStructHydrator implements OrderLineItemStructHydratorInterface
{
    /** @var EntityRepositoryInterface */
    private $productRepository;

    public function __construct(
        EntityRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(OrderLineItemEntity $orderLineItem, CurrencyEntity $currency, Context $context): array
    {
        $precision = $currency->getDecimalPrecision();

        $lineItem = new LineItem();
        $lineItem->assign([
            'type'      => $this->matchType($orderLineItem->getType()),
            'reference' => $this->getReferenceNumber($orderLineItem),
            'name'      => $orderLineItem->getLabel(),
            'quantity'  => $orderLineItem->getQuantity(),
        ]);

        if (null !== $orderLineItem->getPrice()) {
            $totalTaxAmount = $this->getTotalTaxAmount($orderLineItem->getPrice()->getCalculatedTaxes());

            if ($orderLineItem->getType() === CartLineItem::PRODUCT_LINE_ITEM_TYPE) {
                $unit = $this->getUnitNameFromProductId((string) $orderLineItem->getReferencedId(), $context);

                if (!empty($unit)) {
                    $lineItem->assign([
                        'quantityUnit' => $unit,
                    ]);
                }
            }

            $lineItem->assign([
                'precision'      => $precision,
                'unitPrice'      => $orderLineItem->getPrice()->getUnitPrice(),
                'totalAmount'    => $orderLineItem->getPrice()->getTotalPrice(),
                'totalTaxAmount' => $totalTaxAmount,
                'taxRate'        => $this->getTaxRate($orderLineItem->getPrice()),
            ]);
        }

        return [$lineItem];
    }

    private function getTaxRate(CalculatedPrice $price): float
    {
        $taxRate = 0;

        /** @var CalculatedTax $tax */
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

        /** @var CalculatedTax $tax */
        foreach ($taxes as $tax) {
            $totalTaxAmount += $tax->getTax();
        }

        return $totalTaxAmount;
    }

    private function matchType(?string $type): string
    {
        if ($type === ShopwareLineItem::PRODUCT_LINE_ITEM_TYPE) {
            return LineItem::TYPE_PHYSICAL;
        }

        if ($type === ShopwareLineItem::CREDIT_LINE_ITEM_TYPE) {
            return LineItem::TYPE_DISCOUNT;
        }

        if ($type === PromotionProcessor::LINE_ITEM_TYPE) {
            return LineItem::TYPE_DISCOUNT;
        }

        // TODO: Add surcharge as soon as Shopware supports it.

        return LineItem::TYPE_PHYSICAL;
    }

    private function getUnitNameFromProductId(string $productId, Context $context): ?string
    {
        $criteria = new Criteria([$productId]);
        $criteria->addAssociation('unit');

        /** @var null|ProductEntity $entity */
        $entity = $this->productRepository->search($criteria, $context)->first();

        if (null === $entity || null === $entity->getUnit()) {
            return null;
        }

        return $entity->getUnit()->getTranslation('shortCode');
    }

    private function getReferenceNumber(OrderLineItemEntity $orderLineItem): string
    {
        $payload = $orderLineItem->getPayload();

        if ($payload && array_key_exists('productNumber', $payload)) {
            $referenceNumber = $payload['productNumber'];
        } else {
            $referenceNumber = (string) $orderLineItem->getReferencedId();
        }

        return mb_strimwidth($referenceNumber, 0, 255);
    }
}
