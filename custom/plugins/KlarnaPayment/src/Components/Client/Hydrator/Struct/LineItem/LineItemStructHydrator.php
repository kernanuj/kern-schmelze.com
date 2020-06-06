<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\LineItem;

use KlarnaPayment\Components\Client\Hydrator\Struct\ProductIdentifier\ProductIdentifierStructHydratorInterface;
use KlarnaPayment\Components\Client\Struct\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItem as CartLineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItem as ShopwareLineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Promotion\Cart\PromotionProcessor;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

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

    /**
     * {@inheritdoc}
     */
    public function hydrate(LineItemCollection $cartLineItems, SalesChannelContext $context): array
    {
        $precision = $context->getCurrency()->getDecimalPrecision();
        $products  = $this->loadProducts($cartLineItems, $context);

        $lineItems = [];

        /** @var CartLineItem $cartLineItem */
        foreach ($cartLineItems as $cartLineItem) {
            $lineItem = new LineItem();
            $lineItem->assign([
                'type'      => $this->matchType($cartLineItem->getType()),
                'reference' => $this->getReferenceNumber($cartLineItem),
                'name'      => $cartLineItem->getLabel(),
                'quantity'  => $cartLineItem->getQuantity(),
            ]);

            if (null !== $cartLineItem->getCover()) {
                $lineItem->assign([
                    'imageUrl' => $cartLineItem->getCover()->getUrl(),
                ]);
            }

            if (null !== $cartLineItem->getPrice()) {
                $totalTaxAmount = $this->getTotalTaxAmount($cartLineItem->getPrice()->getCalculatedTaxes());

                if ($cartLineItem->getType() === CartLineItem::PRODUCT_LINE_ITEM_TYPE) {
                    /** @var null|ProductEntity $product */
                    $product = $products->get((string) $cartLineItem->getReferencedId());

                    if (null !== $product) {
                        $lineItem->assign([
                            'quantityUnit'      => $this->getUnitNameFromProduct($product),
                            'productIdentifier' => $this->productIdentifierHydrator->hydrate($product, $context),
                        ]);
                    }
                }

                $lineItem->assign([
                    'precision'      => $precision,
                    'unitPrice'      => $cartLineItem->getPrice()->getUnitPrice(),
                    'totalAmount'    => $cartLineItem->getPrice()->getTotalPrice(),
                    'totalTaxAmount' => $totalTaxAmount,
                    'taxRate'        => $this->getTaxRate($cartLineItem->getPrice()),
                ]);
            }

            $lineItems[] = $lineItem;
        }

        return $lineItems;
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

    private function loadProducts(LineItemCollection $cartLineItems, SalesChannelContext $context): EntityCollection
    {
        $products = $cartLineItems->filterType(CartLineItem::PRODUCT_LINE_ITEM_TYPE);

        $criteria = new Criteria($products->getReferenceIds());
        $criteria->addAssociation('unit');
        $criteria->addAssociation('categories');
        $criteria->addAssociation('manufacturer');

        $products = $this->productRepository->search($criteria, $context->getContext());

        if (!$products->count()) {
            return new EntityCollection();
        }

        return $products->getEntities();
    }

    private function getUnitNameFromProduct(ProductEntity $product): ?string
    {
        if (null === $product->getUnit()) {
            return null;
        }

        return $product->getUnit()->getTranslation('shortCode');
    }

    private function getReferenceNumber(ShopwareLineItem $cartLineItem): string
    {
        if ($cartLineItem->hasPayloadValue('productNumber')) {
            $referenceNumber = $cartLineItem->getPayloadValue('productNumber');
        } else {
            $referenceNumber = (string) $cartLineItem->getReferencedId();
        }

        return mb_strimwidth($referenceNumber, 0, 255);
    }
}
