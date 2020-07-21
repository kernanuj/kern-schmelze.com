<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\OrderValidator;

use Shopware\Core\Checkout\Order\OrderEntity;

interface OrderValidatorInterface
{
    public function isKlarnaOrder(OrderEntity $orderEntity): bool;
}
