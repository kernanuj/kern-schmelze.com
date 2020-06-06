<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\Delivery;

use KlarnaPayment\Components\Client\Struct\LineItem;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class DeliveryStructHydrator implements DeliveryStructHydratorInterface
{
    private const NAME = 'SHIPPING_COSTS';

    /**
     * {@inheritdoc}
     */
    public function hydrate(DeliveryCollection $deliveries, SalesChannelContext $context): array
    {
        $lineItems = [];

        foreach ($deliveries as $delivery) {
            if (empty($delivery->getShippingCosts()->getTotalPrice())) {
                return $lineItems;
            }

            $precision = $context->getCurrency()->getDecimalPrecision();

            foreach ($delivery->getShippingCosts()->getCalculatedTaxes() as $tax) {
                $lineItem = new LineItem();
                $lineItem->assign([
                    'type'           => LineItem::TYPE_PHYSICAL,
                    'reference'      => self::NAME,
                    'name'           => self::NAME,
                    'quantity'       => $delivery->getShippingCosts()->getQuantity(),
                    'precision'      => $precision,
                    'unitPrice'      => $tax->getPrice(),
                    'totalAmount'    => $tax->getPrice(),
                    'totalTaxAmount' => $tax->getTax(),
                    'taxRate'        => $tax->getTaxRate(),
                ]);

                $lineItems[] = $lineItem;
            }
        }

        return $lineItems;
    }
}
