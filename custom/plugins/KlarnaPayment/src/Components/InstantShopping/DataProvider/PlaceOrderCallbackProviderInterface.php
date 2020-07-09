<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\DataProvider;

use KlarnaPayment\Components\CartHasher\Exception\InvalidCartHashException;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Exception\OrderRecalculationException;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

interface PlaceOrderCallbackProviderInterface
{
    public function resetDefaultAddresses(array $data): void;

    public function updateCustomer(Cart $cart, Request $request, SalesChannelContext $context): array;

    public function createOrder(Cart $cart, Request $request, RequestDataBag $dataBag, SalesChannelContext $context): void;

    /**
     * @throws InvalidCartHashException
     * @throws OrderRecalculationException
     */
    public function getUpdatedCart(Cart $cart, string $cartHash, SalesChannelContext $context): Cart;

    public function emptyCart(SalesChannelContext $context): void;

    public function loginUser(CustomerEntity $customer, SalesChannelContext $context): void;

    public function getFinishUrl(string $orderId): string;

    public function getCustomerByOrderAndTransactionId(string $orderId, string $transactionId, SalesChannelContext $context): ?OrderCustomerEntity;

    public function deleteTemporaryKlarnaAddresses(SalesChannelContext $context): void;
}
