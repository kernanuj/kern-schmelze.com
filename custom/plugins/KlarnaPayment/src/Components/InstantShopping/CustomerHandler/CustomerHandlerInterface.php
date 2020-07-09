<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\CustomerHandler;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

interface CustomerHandlerInterface
{
    public function setDefaultShippingAddress(string $addressId, CustomerEntity $customer, SalesChannelContext $context): void;

    public function setDefaultBillingAddress(string $addressId, CustomerEntity $customer, SalesChannelContext $context): void;

    public function deleteTemporaryAddresses(array $data, SalesChannelContext $context): void;

    public function getCustomerById(?string $id, SalesChannelContext $context): ?CustomerEntity;

    public function createCustomerAddress(SalesChannelContext $context, array $addressValues, string $customerId, bool $isTemporary = false, array $customerValues = []): string;

    public function createGuestCustomer(array $billingAddress, array $shippingAddress, SalesChannelContext $context, array $customerData = null, bool $isTemporary = false, bool $skipCustomerNumber = true): CustomerEntity;

    public function cleanupGuestCustomer(?CustomerEntity $customer, ?string $cartToken, Context $context): void;

    public function setCustomerPaymentMethod(string $id, CustomerEntity $customer, SalesChannelContext $context): void;

    public function updateTemporaryCustomerAccount(CustomerEntity $customer, Request $request, SalesChannelContext $context): void;

    public function updateContextCustomerAddresses(CustomerEntity $customer, array $billingAddress, array $shippingAddress, string $currencyIso, SalesChannelContext $context): array;
}
