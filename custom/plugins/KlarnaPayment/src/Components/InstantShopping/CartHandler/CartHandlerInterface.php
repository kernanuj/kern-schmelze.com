<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\CartHandler;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Exception\CartTokenNotFoundException;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface CartHandlerInterface
{
    /**
     * @throws CartTokenNotFoundException
     */
    public function getInstantShoppingCartByToken(string $token, SalesChannelContext $context): Cart;

    public function getCustomerIdFromCart(Cart $cart): ?string;

    public function getCustomerIdFromCartToken(string $token): ?string;

    public function getCustomerFromCart(Cart $cart, SalesChannelContext $context): ?CustomerEntity;
}
