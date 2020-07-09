<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Request\CreateSession;

use KlarnaPayment\Components\Client\Hydrator\Struct\Delivery\DeliveryStructHydratorInterface;
use KlarnaPayment\Components\Client\Hydrator\Struct\LineItem\LineItemStructHydratorInterface;
use KlarnaPayment\Components\Client\Request\CreateSessionRequest;
use KlarnaPayment\Components\Client\Struct\Options;
use KlarnaPayment\Components\Helper\PaymentHelper\PaymentHelperInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CreateSessionRequestHydrator implements CreateSessionRequestHydratorInterface
{
    /** @var LineItemStructHydratorInterface */
    private $lineItemHydrator;

    /** @var DeliveryStructHydratorInterface */
    private $deliveryHydrator;

    /** @var PaymentHelperInterface */
    private $paymentHelper;

    /** @var EntityRepositoryInterface */
    private $countryRepository;

    public function __construct(
        LineItemStructHydratorInterface $lineItemHydrator,
        DeliveryStructHydratorInterface $deliveryHydrator,
        PaymentHelperInterface $paymentHelper,
        EntityRepositoryInterface $countryRepository
    ) {
        $this->lineItemHydrator  = $lineItemHydrator;
        $this->deliveryHydrator  = $deliveryHydrator;
        $this->paymentHelper     = $paymentHelper;
        $this->countryRepository = $countryRepository;
    }

    public function hydrate(Cart $cart, SalesChannelContext $context): CreateSessionRequest
    {
        $precision      = $context->getCurrency()->getDecimalPrecision();
        $totalTaxAmount = $this->getTotalTaxAmount($cart->getPrice()->getCalculatedTaxes());

        $options = new Options();
        $options->assign([
            'disable_confirmation_modals' => true,
        ]);

        $request = new CreateSessionRequest();
        $request->assign([
            'purchaseCountry'  => $this->paymentHelper->getShippingCountry($context)->getIso(),
            'purchaseCurrency' => $context->getCurrency()->getIsoCode(),
            'locale'           => $this->paymentHelper->getSalesChannelLocale($context)->getCode(),
            'options'          => $options,
            'precision'        => $precision,
            'orderAmount'      => $cart->getPrice()->getTotalPrice(),
            'orderTaxAmount'   => $totalTaxAmount,
            'orderLines'       => $this->hydrateOrderLines(
                $cart->getLineItems(),
                $cart->getDeliveries(),
                $context->getCurrency(),
                $context->getContext()
            ),
            'salesChannel' => $context->getSalesChannel()->getId(),
        ]);

        if (null !== $context->getCustomer()) {
            $country = $this->getBillingAddressCountry($context->getCustomer(), $context->getContext());

            if (null !== $country) {
                $request->assign([
                    'locale'          => substr_replace($request->getLocale(), $country->getIso(), 3, 2),
                    'purchaseCountry' => $country->getIso(),
                ]);
            }
        }

        return $request;
    }

    private function getTotalTaxAmount(CalculatedTaxCollection $taxes): float
    {
        $totalTaxAmount = 0;

        foreach ($taxes as $tax) {
            $totalTaxAmount += $tax->getTax();
        }

        return $totalTaxAmount;
    }

    private function hydrateOrderLines(LineItemCollection $lineItems, DeliveryCollection $deliveries, CurrencyEntity $currency, Context $context): array
    {
        $orderLines = [];

        foreach ($this->lineItemHydrator->hydrate($lineItems, $currency, $context) as $orderLine) {
            $orderLines[] = $orderLine;
        }

        foreach ($this->deliveryHydrator->hydrate($deliveries, $currency, $context) as $orderLine) {
            $orderLines[] = $orderLine;
        }

        return array_filter($orderLines);
    }

    private function getBillingAddressCountry(CustomerEntity $customer, Context $context): ?CountryEntity
    {
        if (null === $customer->getActiveBillingAddress()) {
            return null;
        }

        $criteria = new Criteria([
            $customer->getActiveBillingAddress()->getCountryId(),
        ]);

        return $this->countryRepository->search($criteria, $context)->first();
    }
}
