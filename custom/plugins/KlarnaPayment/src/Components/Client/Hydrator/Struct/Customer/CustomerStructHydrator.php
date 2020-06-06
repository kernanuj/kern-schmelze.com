<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\Customer;

use KlarnaPayment\Components\Client\Struct\Customer;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CustomerStructHydrator implements CustomerStructHydratorInterface
{
    private const TYPE_PERSON       = 'person';
    private const TYPE_ORGANIZATION = 'organization';

    public function hydrate(SalesChannelContext $context): ?Customer
    {
        if (null === $context->getCustomer()) {
            return null;
        }

        $customer = new Customer();

        if (null !== $context->getCustomer()->getBirthday()) {
            $customer->assign([
                'birthday' => $context->getCustomer()->getBirthday(),
            ]);
        }

        $billingAddress = $this->getBillingAddress($context);

        if (null === $billingAddress) {
            return $customer;
        }

        if (!empty($billingAddress->getCompany())) {
            $customer->assign([
                'type' => self::TYPE_ORGANIZATION,
            ]);
        } else {
            $customer->assign([
                'type' => self::TYPE_PERSON,
            ]);
        }

        $customer->assign([
            'vatId' => $billingAddress->getVatId(),
            'title' => $billingAddress->getTitle(),
        ]);

        return $customer;
    }

    private function getBillingAddress(SalesChannelContext $context): ?CustomerAddressEntity
    {
        if (null === $context->getCustomer()) {
            return null;
        }

        $billingAddress = $context->getCustomer()->getActiveBillingAddress();

        if (null === $billingAddress) {
            return null;
        }

        return $billingAddress;
    }
}
