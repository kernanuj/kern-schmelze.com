<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\CustomerHandler;

use Doctrine\DBAL\Connection;
use KlarnaPayment\Components\Extension\GuestCustomerRegistrationExtension;
use KlarnaPayment\Components\Helper\CurrencyHelper\CurrencyHelperInterface;
use KlarnaPayment\Components\InstantShopping\ContextHandler\ContextHandlerInterface;
use LogicException;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Customer\Exception\CustomerNotFoundException;
use Shopware\Core\Checkout\Customer\SalesChannel\AccountRegistrationService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\System\Country\Exception\CountryNotFoundException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerHandler implements CustomerHandlerInterface
{
    public const KLARNA_INSTANT_SHOPPING_TEMPORARY_IDENTIFYING_VALUE = '0e9d7933f84244879a78acfc5b8a8d99';
    public const TEMPORARY_ZIP                                       = 'Example ZIP';

    private const TEMPORARY_LAST_NAME         = 'Klarna Guest Account';
    private const TEMPORARY_STREET            = 'Examplestreet 314';
    private const TEMPORARY_CITY              = 'Example City';
    private const TEMPORARY_PHONE_NUMBER      = '01234567890';
    private const TEMPORARY_ADDITIONAL_LINE_1 = 'Additional 1';
    private const TEMPORARY_ADDITIONAL_LINE_2 = 'Additional 2';

    /** @var EntityRepositoryInterface */
    private $countryRepository;

    /** @var EntityRepositoryInterface */
    private $salutationRepository;

    /** @var EntityRepositoryInterface */
    private $customerRepository;

    /** @var EntityRepositoryInterface */
    private $customerAddressRepository;

    /** @var SystemConfigService */
    private $systemConfigService;

    /** @var AccountRegistrationService */
    private $accountRegistrationService;

    /** @var ContextHandlerInterface */
    private $contextHandler;

    /** @var CurrencyHelperInterface */
    private $currencyHelper;

    /** @var Connection */
    private $connection;

    public function __construct(
        EntityRepositoryInterface $countryRepository,
        EntityRepositoryInterface $salutationRepository,
        EntityRepositoryInterface $customerRepository,
        EntityRepositoryInterface $customerAddressRepository,
        SystemConfigService $systemConfigService,
        AccountRegistrationService $accountRegistrationService,
        ContextHandlerInterface $contextHandler,
        CurrencyHelperInterface $currencyHelper,
        Connection $connection
    ) {
        $this->countryRepository          = $countryRepository;
        $this->salutationRepository       = $salutationRepository;
        $this->customerRepository         = $customerRepository;
        $this->customerAddressRepository  = $customerAddressRepository;
        $this->systemConfigService        = $systemConfigService;
        $this->accountRegistrationService = $accountRegistrationService;
        $this->contextHandler             = $contextHandler;
        $this->currencyHelper             = $currencyHelper;
        $this->connection                 = $connection;
    }

    public function createCustomerAddress(SalesChannelContext $context, array $addressValues, string $customerId, bool $isTemporary = false, array $customerValues = []): string
    {
        $addressId = $this->getExistingCustomerAddressId($addressValues, $customerId, $context, $isTemporary);

        if (!empty($addressId)) {
            return $addressId;
        }

        $id     = Uuid::randomHex();
        $gender = '';

        if (array_key_exists('gender', $customerValues)) {
            $gender = $customerValues['gender'];
        }

        $addressData = [
            'id'                     => $id,
            'customerId'             => $customerId,
            'salutationId'           => $this->getSalutationIdByGender($gender, $context),
            'firstName'              => ($isTemporary === false) ? $addressValues['given_name'] : self::KLARNA_INSTANT_SHOPPING_TEMPORARY_IDENTIFYING_VALUE,
            'lastName'               => ($isTemporary === false) ? $addressValues['family_name'] : self::TEMPORARY_LAST_NAME,
            'street'                 => ($isTemporary === false) ? $addressValues['street_address'] : self::TEMPORARY_STREET,
            'countryId'              => $this->getCountryIdByIso($addressValues['country'], $context),
            'countryStateId'         => null,
            'zipcode'                => $addressValues['postal_code'],
            'city'                   => array_key_exists('city', $addressValues) ? $addressValues['city'] : self::TEMPORARY_CITY,
            'phoneNumber'            => array_key_exists('phone', $addressValues) ? $addressValues['phone'] : '',
            'vatId'                  => '',
            'additionalAddressLine1' => array_key_exists('street_address2', $addressValues) ? $addressValues['street_address2'] : '',
            'additionalAddressLine2' => '',
        ];

        $this->customerAddressRepository->upsert([$addressData], $context->getContext());

        return $id;
    }

    public function createGuestCustomer(array $billingAddress, array $shippingAddress, SalesChannelContext $context, array $customerData = null, bool $isTemporary = false, bool $skipCustomerNumber = true): CustomerEntity
    {
        $gender = 'not_specified';

        if (!empty($customerData) && array_key_exists('gender', $customerData)) {
            $gender = $customerData['gender'];
        }

        $this->setDefaultValuesForRequiredFields($billingAddress, $shippingAddress);

        // Caution: Addresses can be as little as just the country and postal code
        $data = [
            'salutationId'   => $this->getSalutationIdByGender($gender, $context),
            'firstName'      => ($isTemporary === false) ? $billingAddress['given_name'] : self::KLARNA_INSTANT_SHOPPING_TEMPORARY_IDENTIFYING_VALUE,
            'lastName'       => ($isTemporary === false) ? $billingAddress['family_name'] : self::TEMPORARY_LAST_NAME,
            'password'       => Uuid::randomHex(),
            'email'          => ($isTemporary === false) ? $billingAddress['email'] : Uuid::randomHex() . '@example.com',
            'title'          => '',
            'active'         => true,
            'billingAddress' => new DataBag([
                'salutationId'           => $this->getSalutationIdByGender($gender, $context),
                'firstName'              => ($isTemporary === false) ? $billingAddress['given_name'] : self::KLARNA_INSTANT_SHOPPING_TEMPORARY_IDENTIFYING_VALUE,
                'lastName'               => ($isTemporary === false) ? $billingAddress['family_name'] : self::TEMPORARY_LAST_NAME,
                'countryId'              => $this->getCountryIdByIso($billingAddress['country'], $context),
                'street'                 => ($isTemporary === false) ? $billingAddress['street_address'] : self::TEMPORARY_STREET,
                'zipcode'                => array_key_exists('postal_code', $billingAddress) ? $billingAddress['postal_code'] : self::TEMPORARY_ZIP,
                'city'                   => array_key_exists('city', $billingAddress) ? $billingAddress['city'] : self::TEMPORARY_CITY,
                'phoneNumber'            => array_key_exists('phone', $billingAddress) ? $billingAddress['phone'] : '',
                'vatId'                  => '',
                'additionalAddressLine1' => array_key_exists('street_address2', $billingAddress) ? $billingAddress['street_address2'] : '',
                'additionalAddressLine2' => array_key_exists('additional_address_line_2', $billingAddress) ? $billingAddress['additional_address_line_2'] : '',
            ]),
            'shippingAddress' => new DataBag([
                'salutationId'           => $this->getSalutationIdByGender($gender, $context),
                'countryId'              => $this->getCountryIdByIso($shippingAddress['country'], $context),
                'firstName'              => ($isTemporary === false) ? $shippingAddress['given_name'] : self::KLARNA_INSTANT_SHOPPING_TEMPORARY_IDENTIFYING_VALUE,
                'lastName'               => ($isTemporary === false) ? $shippingAddress['family_name'] : self::TEMPORARY_LAST_NAME,
                'street'                 => ($isTemporary === false) ? $shippingAddress['street_address'] : self::TEMPORARY_STREET,
                'zipcode'                => array_key_exists('postal_code', $shippingAddress) ? $shippingAddress['postal_code'] : self::TEMPORARY_ZIP,
                'city'                   => array_key_exists('city', $shippingAddress) ? $shippingAddress['city'] : self::TEMPORARY_CITY,
                'phoneNumber'            => array_key_exists('phone', $shippingAddress) ? $shippingAddress['phone'] : '',
                'additionalAddressLine1' => array_key_exists('street_address2', $shippingAddress) ? $shippingAddress['street_address2'] : '',
                'additionalAddressLine2' => array_key_exists('additional_address_line_2', $shippingAddress) ? $shippingAddress['additional_address_line_2'] : '',
            ]),
        ];

        if (!empty($customerData) && array_key_exists('date_of_birth', $customerData)) {
            $birthData             = explode('-', $customerData['date_of_birth']);
            $data['birthdayYear']  = $birthData[0];
            $data['birthdayMonth'] = $birthData[1];
            $data['birthdayDay']   = $birthData[2];
        }

        $dataBag = new DataBag();
        $dataBag->add($data);

        if ($skipCustomerNumber) {
            $context->getContext()->addExtension(GuestCustomerRegistrationExtension::EXTENSION_NAME, new GuestCustomerRegistrationExtension());
        }
        // TODO: Use \Shopware\Core\Checkout\Customer\SalesChannel\AbstractRegisterRoute before 6.4, deprecated from 6.2
        $customerId = $this->accountRegistrationService->register($dataBag, true, $context, $this->getAdditionalRegisterValidationDefinitions($dataBag, $context));
        $context->getContext()->removeExtension(GuestCustomerRegistrationExtension::EXTENSION_NAME);

        $customer = $this->getCustomerById($customerId, $context);

        if (!$customer) {
            throw new CustomerNotFoundException($data['email']);
        }

        return $customer;
    }

    public function cleanupGuestCustomer(?CustomerEntity $customer, ?string $cartToken, Context $context): void
    {
        if ($customer && $this->isGuestCustomer($customer)) {
            if ($cartToken) {
                $this->connection->executeQuery(
                    'UPDATE `cart` SET `customer_id` = NULL WHERE `token` = :token',
                    ['token' => $cartToken]
                );
            }
            $this->customerRepository->delete([['id' => $customer->getId()]], $context);
        }
    }

    public function setDefaultShippingAddress(string $addressId, CustomerEntity $customer, SalesChannelContext $context): void
    {
        $data = [
            'id'                       => $customer->getId(),
            'defaultShippingAddressId' => $addressId,
        ];
        $this->customerRepository->update([$data], $context->getContext());
    }

    public function setDefaultBillingAddress(string $addressId, CustomerEntity $customer, SalesChannelContext $context): void
    {
        $data = [
            'id'                      => $customer->getId(),
            'defaultBillingAddressId' => $addressId,
        ];
        $this->customerRepository->update([$data], $context->getContext());
    }

    public function setCustomerPaymentMethod(string $id, CustomerEntity $customer, SalesChannelContext $context): void
    {
        $data = [
            'id'                     => $customer->getId(),
            'defaultPaymentMethodId' => $id,
        ];

        $this->customerRepository->update([$data], $context->getContext());
    }

    public function deleteTemporaryAddresses(array $data, SalesChannelContext $context): void
    {
        if (!empty($data['temporaryShippingAddressId'])) {
            $this->customerAddressRepository->delete([['id' => $data['temporaryShippingAddressId']]], $context->getContext());
        }

        if (!empty($data['temporaryBillingAddressId'])) {
            $this->customerAddressRepository->delete([['id' => $data['temporaryBillingAddressId']]], $context->getContext());
        }
    }

    public function updateTemporaryCustomerAccount(CustomerEntity $customer, Request $request, SalesChannelContext $context): void
    {
        if ($customer->getFirstName() !== self::KLARNA_INSTANT_SHOPPING_TEMPORARY_IDENTIFYING_VALUE) {
            return;
        }

        $data = [
            'id'        => $customer->getId(),
            'firstName' => $request->get('order')['billing_address']['given_name'],
            'lastName'  => $request->get('order')['billing_address']['family_name'],
            'email'     => $request->get('order')['billing_address']['email'],
        ];

        $customerData = $request->get('order')['customer'];

        if (!empty($customerData) && array_key_exists('date_of_birth', $customerData)) {
            $birthData             = explode('-', $customerData['date_of_birth']);
            $data['birthdayYear']  = $birthData[0];
            $data['birthdayMonth'] = $birthData[1];
            $data['birthdayDay']   = $birthData[2];
        }

        $this->customerRepository->update([$data], $context->getContext());
    }

    public function getCustomerById(?string $id, SalesChannelContext $context): ?CustomerEntity
    {
        if (empty($id)) {
            return null;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $id));
        $result = $this->customerRepository->search($criteria, $context->getContext());

        /** @noinspection IsEmptyFunctionUsageInspection */
        if (empty($result)) {
            return null;
        }

        return $result->first();
    }

    public function updateContextCustomerAddresses(CustomerEntity $customer, array $billingAddress, array $shippingAddress, string $currencyIso, SalesChannelContext $context): array
    {
        $data = [
            'context'                    => $context,
            'temporaryShippingAddressId' => '',
            'temporaryBillingAddressId'  => '',
        ];

        if ($this->isAddressUpdateRequired($customer->getActiveBillingAddress(), $billingAddress)) {
            $billingAddressId                  = $this->createCustomerAddress($context, $billingAddress, $customer->getId(), true);
            $data['temporaryBillingAddressId'] = $billingAddressId;
        } else {
            /** @var CustomerAddressEntity $activeBillingAddress */
            $activeBillingAddress = $customer->getActiveBillingAddress();
            $billingAddressId     = $activeBillingAddress->getId();
        }

        if ($this->isAddressUpdateRequired($customer->getActiveShippingAddress(), $shippingAddress)) {
            $shippingAddressId                  = $this->createCustomerAddress($context, $shippingAddress, $customer->getId(), true);
            $data['temporaryShippingAddressId'] = $shippingAddressId;
        } else {
            /** @var CustomerAddressEntity $activeShippingAddress */
            $activeShippingAddress = $customer->getActiveShippingAddress();
            $shippingAddressId     = $activeShippingAddress->getId();
        }

        $this->setDefaultShippingAddress($shippingAddressId, $customer, $context);
        $this->setDefaultBillingAddress($billingAddressId, $customer, $context);

        $data['context'] = $this->contextHandler->createSalesChannelContext(
            Random::getAlphanumericString(32),
            $customer->getId(),
            $this->currencyHelper->getCurrencyIdFromIso($currencyIso, $context->getContext()),
            null,
            $context
        );

        return $data;
    }

    private function isAddressUpdateRequired(?CustomerAddressEntity $address, array $addressValues): bool
    {
        if (!$address || $address->getCountry() === null) {
            return true;
        }

        if ((array_key_exists('city', $addressValues) && $address->getCity() !== $addressValues['city'])
            || $address->getZipcode() !== $addressValues['postal_code']
            || $address->getCountry()->getIso() !== $addressValues['country']
        ) {
            return true;
        }

        return false;
    }

    private function getExistingCustomerAddressId(array $addressValues, string $customerId, SalesChannelContext $context, bool $isTemporary = false): ?string
    {
        if ($isTemporary === true) {
            return null;
        }

        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('customerId', $customerId),
            new EqualsFilter('firstName', $addressValues['given_name']),
            new EqualsFilter('lastName', $addressValues['family_name']),
            new EqualsFilter('street', $addressValues['street_address']),
            new EqualsFilter('city', $addressValues['city']),
            new EqualsFilter('zipcode', $addressValues['postal_code'])
        );

        /** @var null|CustomerAddressEntity $address */
        $address = $this->customerAddressRepository->search($criteria, $context->getContext())->first();

        return $address ? $address->getId() : null;
    }

    /**
     * @throws CountryNotFoundException
     * @throws InconsistentCriteriaIdsException
     */
    private function getCountryIdByIso(string $iso, SalesChannelContext $context): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('iso', $iso));
        $country = $this->countryRepository->search($criteria, $context->getContext())->first();

        if (empty($country)) {
            throw new CountryNotFoundException('Country not available: ' . $iso);
        }

        return $country->getId();
    }

    private function getSalutationIdByGender(string $gender, SalesChannelContext $context): string
    {
        $criteria = new Criteria();

        switch ($gender) {
            case 'male':
                $search = 'mr';

                break;
            case 'female':
                $search = 'mrs';

                break;
            default:
                $search = 'not_specified';

                break;
        }

        $criteria->addFilter(new EqualsFilter('salutationKey', $search));

        $salutation = $this->salutationRepository->search($criteria, $context->getContext())->first();

        if (empty($salutation)) {
            throw new LogicException(sprintf('Salutation %s not found', $search));
        }

        return $salutation->getId();
    }

    private function getAdditionalRegisterValidationDefinitions(DataBag $data, SalesChannelContext $context): DataValidationDefinition
    {
        $definition = new DataValidationDefinition('storefront.confirmation');

        if ($this->systemConfigService->get('core.loginRegistration.requireEmailConfirmation', $context->getSalesChannel()->getId())) {
            $definition->add('emailConfirmation', new NotBlank(), new EqualTo([
                'value' => $data->get('email'),
            ]));
        }

        if ($this->systemConfigService->get('core.loginRegistration.requirePasswordConfirmation', $context->getSalesChannel()->getId())) {
            $definition->add('passwordConfirmation', new NotBlank(), new EqualTo([
                'value' => $data->get('password'),
            ]));
        }

        return $definition;
    }

    private function isGuestCustomer(CustomerEntity $customer): bool
    {
        return $customer->getFirstName() === self::KLARNA_INSTANT_SHOPPING_TEMPORARY_IDENTIFYING_VALUE;
    }

    private function setDefaultValuesForRequiredFields(array &$billingAddress, array &$shippingAddress): void
    {
        if ($this->systemConfigService->get('core.loginRegistration.phoneNumberFieldRequired')) {
            if (!array_key_exists('phone', $billingAddress)) {
                $billingAddress['phone'] = self::TEMPORARY_PHONE_NUMBER;
            }

            if (!array_key_exists('phone', $shippingAddress)) {
                $shippingAddress['phone'] = self::TEMPORARY_PHONE_NUMBER;
            }
        }

        if ($this->systemConfigService->get('core.loginRegistration.additionalAddressField1Required')) {
            if (!array_key_exists('street_address2', $billingAddress)) {
                $billingAddress['street_address2'] = self::TEMPORARY_ADDITIONAL_LINE_1;
            }

            if (!array_key_exists('street_address2', $shippingAddress)) {
                $shippingAddress['street_address2'] = self::TEMPORARY_ADDITIONAL_LINE_1;
            }
        }

        if ($this->systemConfigService->get('core.loginRegistration.additionalAddressField2Required')) {
            if (!array_key_exists('additional_address_line_2', $billingAddress)) {
                $billingAddress['additional_address_line_2'] = self::TEMPORARY_ADDITIONAL_LINE_2;
            }

            if (!array_key_exists('additional_address_line_2', $shippingAddress)) {
                $shippingAddress['additional_address_line_2'] = self::TEMPORARY_ADDITIONAL_LINE_2;
            }
        }
    }
}
