<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

interface OrderFetcherInterface
{
    public function getOrderFromOrderAddress(string $orderAddressId, Context $context): ?OrderEntity;

    public function getOrderFromLineItem(string $lineItemId, Context $context): ?OrderEntity;

    public function getOrderFromOrder(string $orderId, Context $context): ?OrderEntity;
}
