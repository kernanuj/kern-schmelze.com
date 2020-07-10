<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\Address;

use KlarnaPayment\Components\Client\Struct\Address;
use LogicException;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\Salutation\SalutationEntity;

class AddressStructHydrator implements AddressStructHydratorInterface
{
    /** @var EntityRepositoryInterface */
    private $salutationRepository;

    /** @var EntityRepositoryInterface */
    private $countryRepository;

    /** @var CountryEntity[] */
    private $countries;

    /** @var SalutationEntity[] */
    private $salutations;

    public function __construct(
        EntityRepositoryInterface $salutationRepository,
        EntityRepositoryInterface $countryRepository
    ) {
        $this->salutationRepository = $salutationRepository;
        $this->countryRepository    = $countryRepository;
    }

    public function hydrateFromContext(SalesChannelContext $context, string $type = self::TYPE_BILLING): ?Address
    {
        $customer = $context->getCustomer();

        if (null === $customer) {
            return null;
        }

        if ($type === self::TYPE_BILLING) {
            $customerAddress = $customer->getActiveBillingAddress();
        } elseif ($type === self::TYPE_SHIPPING) {
            $customerAddress = $customer->getActiveShippingAddress();
        } else {
            throw new LogicException('unsupported customer address type');
        }

        if (null === $customerAddress) {
            return null;
        }

        $address = new Address();
        $address->assign([
            'companyName'    => $customerAddress->getCompany(),
            'salutation'     => $this->getCustomerSalutation($customerAddress, $context->getContext())->getDisplayName(),
            'firstName'      => $customerAddress->getFirstName(),
            'lastName'       => $customerAddress->getLastName(),
            'postalCode'     => $customerAddress->getZipcode(),
            'streetAddress'  => $customerAddress->getStreet(),
            'streetAddress2' => $this->getStreetAddress2($customerAddress->getAdditionalAddressLine1(), $customerAddress->getAdditionalAddressLine2()),
            'city'           => $customerAddress->getCity(),
            'country'        => $this->getCustomerCountry($customerAddress, $context->getContext())->getIso(),
            'email'          => $customer->getEmail(),
            'phoneNumber'    => $customerAddress->getPhoneNumber(),
        ]);

        return $address;
    }

    public function hydrateFromOrderAddress(?OrderAddressEntity $address, ?OrderCustomerEntity $customer): ?Address
    {
        if (!$address || !$customer) {
            throw new LogicException('Address or customer missing');
        }

        $addressStruct = new Address();
        $addressStruct->assign([
            'companyName'    => $address->getCompany(),
            'salutation'     => $address->getSalutation() instanceof SalutationEntity ? $address->getSalutation()->getDisplayName() : '',
            'firstName'      => $address->getFirstName(),
            'lastName'       => $address->getLastName(),
            'postalCode'     => $address->getZipcode(),
            'streetAddress'  => $address->getStreet(),
            'streetAddress2' => $this->getStreetAddress2($address->getAdditionalAddressLine1(), $address->getAdditionalAddressLine2()),
            'city'           => $address->getCity(),
            'country'        => $address->getCountry() instanceof CountryEntity ? $address->getCountry()->getIso() : '',
            'email'          => $customer->getEmail(),
            'phoneNumber'    => $address->getPhoneNumber() ?? 'No number provided',
        ]);

        return $addressStruct;
    }

    private function getStreetAddress2(?string $line1, ?string $line2): string
    {
        $streetAddress2 = (string) $line1;

        if (!empty($line2)) {
            $streetAddress2 .= ' - ' . $line2;
        }

        return $streetAddress2;
    }

    private function getCustomerCountry(CustomerAddressEntity $customerAddress, Context $context): CountryEntity
    {
        if (isset($this->countries[$customerAddress->getCountryId()])) {
            return $this->countries[$customerAddress->getCountryId()];
        }

        $criteria = new Criteria([$customerAddress->getCountryId()]);

        /** @var null|CountryEntity $country */
        $country = $this->countryRepository->search($criteria, $context)->first();

        if (null === $country) {
            throw new LogicException('missing order customer country');
        }

        $this->countries[$customerAddress->getCountryId()] = $country;

        return $country;
    }

    private function getCustomerSalutation(CustomerAddressEntity $customerAddress, Context $context): SalutationEntity
    {
        if (isset($this->salutations[$customerAddress->getSalutationId()])) {
            return $this->salutations[$customerAddress->getSalutationId()];
        }

        $criteria = new Criteria([$customerAddress->getSalutationId()]);

        /** @var null|SalutationEntity $salutation */
        $salutation = $this->salutationRepository->search($criteria, $context)->first();

        if (null === $salutation) {
            throw new LogicException('missing order customer salutation');
        }

        $this->salutations[$customerAddress->getSalutationId()] = $salutation;

        return $salutation;
    }
}
