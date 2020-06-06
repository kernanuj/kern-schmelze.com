<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\Delivery;

use KlarnaPayment\Components\Client\Struct\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\System\Currency\CurrencyEntity;

class OrderDeliveryStructHydrator implements OrderDeliveryStructHydratorInterface
{
    private const NAME = 'SHIPPING_COSTS';

    /**
     * {@inheritdoc}
     */
    public function hydrate(OrderDeliveryEntity $delivery, CurrencyEntity $currency): array
    {
        $lineItems = [];

        if (empty($delivery->getShippingCosts()->getTotalPrice())) {
            return $lineItems;
        }

        $precision = $currency->getDecimalPrecision();

        foreach ($delivery->getShippingCosts()->getCalculatedTaxes() as $tax) {
            $lineItem = new LineItem();
            $lineItem->assign([
                'type'           => LineItem::TYPE_PHYSICAL,
                'reference'      => self::NAME,
                'name'           => self::NAME,
                'quantity'       => $delivery->getShippingCosts()->getQuantity(),
                'unitPrice'      => $tax->getPrice(),
                'totalAmount'    => $tax->getPrice(),
                'totalTaxAmount' => $tax->getTax(),
                'taxRate'        => $tax->getTaxRate(),
            ]);

            $lineItems[] = $lineItem;
        }

        return $lineItems;
    }
}
