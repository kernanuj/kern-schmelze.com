<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Request\UpdateAddress;

use KlarnaPayment\Components\Client\Request\UpdateAddressRequest;
use KlarnaPayment\Components\Client\Struct\Address;
use LogicException;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class UpdateAddressRequestHydrator implements UpdateAddressRequestHydratorInterface
{
    public function hydrate(OrderEntity $orderEntity, Context $context): UpdateAddressRequest
    {
        if (null === $orderEntity->getAddresses() || null === $orderEntity->getOrderCustomer()) {
            throw new LogicException('could not find order via id');
        }

        $billingAddress = $orderEntity->getAddresses()->get($orderEntity->getBillingAddressId());

        if (null === $billingAddress) {
            throw new LogicException('could not load billing address from order');
        }

        $shippingAddress = $this->getOrderShippingAddress($orderEntity);

        if (null === $shippingAddress) {
            $shippingAddress = $billingAddress;
        }

        $request = new UpdateAddressRequest();
        $request->assign([
            'orderId'         => $this->getKlarnaOrderId($orderEntity),
            'salesChannel'    => $orderEntity->getSalesChannelId(),
            'billingAddress'  => $this->hydrateAddress($orderEntity->getOrderCustomer(), $billingAddress),
            'shippingAddress' => $this->hydrateAddress($orderEntity->getOrderCustomer(), $shippingAddress),
        ]);

        return $request;
    }

    private function hydrateAddress(OrderCustomerEntity $customer, OrderAddressEntity $customerAddress): Address
    {
        $address = new Address();
        $address->assign([
            'companyName'    => $customerAddress->getCompany(),
            'salutation'     => $this->getCustomerSalutation($customerAddress),
            'firstName'      => $customerAddress->getFirstName(),
            'lastName'       => $customerAddress->getLastName(),
            'postalCode'     => $customerAddress->getZipcode(),
            'streetAddress'  => $customerAddress->getStreet(),
            'streetAddress2' => $this->getStreetAddress2($customerAddress),
            'city'           => $customerAddress->getCity(),
            'country'        => $this->getCustomerCountry($customerAddress),
            'email'          => $customer->getEmail(),
            'phoneNumber'    => $customerAddress->getPhoneNumber(),
        ]);

        return $address;
    }

    private function getStreetAddress2(OrderAddressEntity $customerAddress): ?string
    {
        $streetAddress2 = $customerAddress->getAdditionalAddressLine1();

        if (!empty($customerAddress->getAdditionalAddressLine2())) {
            $streetAddress2 .= ' - ' . $customerAddress->getAdditionalAddressLine2();
        }

        return $streetAddress2;
    }

    private function getCustomerSalutation(OrderAddressEntity $customerAddress): string
    {
        $salutation = $customerAddress->getSalutation();

        if (null === $salutation || null === $salutation->getDisplayName()) {
            throw new LogicException('missing order customer salutation');
        }

        return $salutation->getDisplayName();
    }

    private function getCustomerCountry(OrderAddressEntity $customerAddress): string
    {
        $country = $customerAddress->getCountry();

        if (null === $country || null === $country->getIso()) {
            throw new LogicException('missing order customer country');
        }

        return $country->getIso();
    }

    private function getOrderShippingAddress(OrderEntity $orderEntity): ?OrderAddressEntity
    {
        /** @var OrderDeliveryEntity[] $deliveries */
        $deliveries = $orderEntity->getDeliveries();

        // TODO: Only one shipping address is supported currently, this could change in the future
        foreach ($deliveries as $delivery) {
            if ($delivery->getShippingOrderAddress() === null) {
                continue;
            }

            return $delivery->getShippingOrderAddress();
        }

        return null;
    }

    private function getKlarnaOrderId(OrderEntity $orderEntity): string
    {
        /** @var OrderTransactionEntity[] $transactions */
        $transactions = $orderEntity->getTransactions();

        // TODO: Only one transaction per order is supported, this could change in the future.
        foreach ($transactions as $transaction) {
            if (empty($transaction->getCustomFields()['klarna_order_id'])) {
                continue;
            }

            return $transaction->getCustomFields()['klarna_order_id'];
        }

        throw new LogicException('could not locate the klarna_order_id field in any order transaction');
    }
}
