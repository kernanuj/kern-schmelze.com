<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\OrderHandler;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

interface OrderHandlerInterface
{
    public function createOrder(Cart $cart, Request $request, RequestDataBag $dataBag, SalesChannelContext $context): ?RedirectResponse;

    public function getOrderByOrderAndTransactionId(string $orderId, string $transactionId, SalesChannelContext $context): OrderEntity;
}
