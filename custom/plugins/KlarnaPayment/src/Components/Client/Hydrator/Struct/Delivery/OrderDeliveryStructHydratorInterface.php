<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\Delivery;

use KlarnaPayment\Components\Client\Struct\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\System\Currency\CurrencyEntity;

interface OrderDeliveryStructHydratorInterface
{
    /**
     * @return LineItem[]
     */
    public function hydrate(OrderDeliveryEntity $delivery, CurrencyEntity $currency): array;
}
