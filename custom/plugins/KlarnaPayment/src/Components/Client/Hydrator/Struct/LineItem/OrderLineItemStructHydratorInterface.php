<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\LineItem;

use KlarnaPayment\Components\Client\Struct\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Currency\CurrencyEntity;

interface OrderLineItemStructHydratorInterface
{
    /**
     * @return LineItem[]
     */
    public function hydrate(OrderLineItemEntity $orderLineItem, CurrencyEntity $currency, Context $context): array;
}
