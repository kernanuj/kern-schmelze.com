<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Request\CreateOrder;

use KlarnaPayment\Components\Client\Hydrator\Struct\Address\AddressStructHydratorInterface;
use KlarnaPayment\Components\Client\Hydrator\Struct\Customer\CustomerStructHydratorInterface;
use KlarnaPayment\Components\Client\Hydrator\Struct\Delivery\DeliveryStructHydratorInterface;
use KlarnaPayment\Components\Client\Hydrator\Struct\LineItem\LineItemStructHydratorInterface;
use KlarnaPayment\Components\Client\Request\CreateOrderRequest;
use KlarnaPayment\Components\Client\Struct\Attachment;
use KlarnaPayment\Components\Client\Struct\Options;
use KlarnaPayment\Components\ConfigReader\ConfigReaderInterface;
use KlarnaPayment\Components\Helper\OrderFetcherInterface;
use KlarnaPayment\Components\Helper\PaymentHelper\PaymentHelperInterface;
use KlarnaPayment\Components\Struct\ExtraMerchantData;
use LogicException;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\RouterInterface;

class CreateOrderRequestHydrator implements CreateOrderRequestHydratorInterface
{
    /** @var LineItemStructHydratorInterface */
    private $lineItemHydrator;

    /** @var DeliveryStructHydratorInterface */
    private $deliveryHydrator;

    /** @var AddressStructHydratorInterface */
    private $addressHydrator;

    /** @var CustomerStructHydratorInterface */
    private $customerHydrator;

    /** @var PaymentHelperInterface */
    private $paymentHelper;

    /** @var OrderConverter */
    private $orderConverter;

    /** @var OrderFetcherInterface */
    private $orderFetcher;

    /** @var RouterInterface */
    private $router;

    /** @var ConfigReaderInterface */
    private $configReader;

    public function __construct(
        LineItemStructHydratorInterface $lineItemHydrator,
        DeliveryStructHydratorInterface $deliveryHydrator,
        AddressStructHydratorInterface $addressHydrator,
        CustomerStructHydratorInterface $customerHydrator,
        PaymentHelperInterface $paymentHelper,
        OrderConverter $orderConverter,
        OrderFetcherInterface $orderFetcher,
        RouterInterface $router,
        ConfigReaderInterface $configReader
    ) {
        $this->lineItemHydrator = $lineItemHydrator;
        $this->deliveryHydrator = $deliveryHydrator;
        $this->addressHydrator  = $addressHydrator;
        $this->customerHydrator = $customerHydrator;
        $this->paymentHelper    = $paymentHelper;
        $this->orderConverter   = $orderConverter;
        $this->orderFetcher     = $orderFetcher;
        $this->router           = $router;
        $this->configReader     = $configReader;
    }

    public function hydrate(
        SyncPaymentTransactionStruct $transaction,
        RequestDataBag $dataBag,
        SalesChannelContext $context
    ): CreateOrderRequest {
        $order = $this->orderFetcher->getOrderFromOrder($transaction->getOrder()->getId(), $context->getContext());

        if (null === $order) {
            throw new LogicException('could not find order via id');
        }

        $cart = $this->orderConverter->convertToCart($order, $context->getContext());

        $totalTaxAmount = $this->getTotalTaxAmount($cart->getPrice()->getCalculatedTaxes());

        $options = new Options();
        $options->assign([
            'disable_confirmation_modals' => true,
        ]);

        if ($order->getAddresses() === null) {
            throw new LogicException('Order has no addresses');
        }
        $billingAddress = $order->getAddresses()->get($order->getBillingAddressId());

        if ($billingAddress === null) {
            throw new LogicException('Order has no billing address');
        }

        if ($billingAddress->getCountry() === null) {
            throw new LogicException('Address has no country');
        }

        if ($order->getOrderCustomer() === null) {
            throw new LogicException('Order has no customer');
        }

        if ($order->getDeliveries() === null) {
            throw new LogicException('Order has no deliveries');
        }

        $delivery = $order->getDeliveries()->first();

        if (!$delivery instanceof OrderDeliveryEntity) {
            throw new LogicException('Order has no deliveries');
        }

        $request = new CreateOrderRequest();
        $request->assign([
            'authorizationToken' => $dataBag->get('klarnaAuthorizationToken'),
            'orderNumber'        => $order->getOrderNumber(),
            'purchaseCountry'    => $billingAddress->getCountry()->getIso(),
            'locale'             => substr_replace($this->paymentHelper->getSalesChannelLocale($context)->getCode(), $billingAddress->getCountry()->getIso(), 3, 2),
            'purchaseCurrency'   => $context->getCurrency()->getIsoCode(),
            'options'            => $options,
            'precision'          => $context->getCurrency()->getDecimalPrecision(),
            'orderAmount'        => $order->getPrice()->getTotalPrice(),
            'orderTaxAmount'     => $totalTaxAmount,
            'orderLines'         => $this->hydrateOrderLines($cart, $context),
            'salesChannel'       => $context->getSalesChannel()->getId(),
            'merchantUrls'       => $this->getMerchantUrls($transaction),
            'billingAddress'     => $this->addressHydrator->hydrateFromOrderAddress($billingAddress, $order->getOrderCustomer()),
            // TODO: Only one shipping address is supported currently, this could change in the future
            'shippingAddress' => $this->addressHydrator->hydrateFromOrderAddress($delivery->getShippingOrderAddress(), $order->getOrderCustomer()),
            'customer'        => $this->customerHydrator->hydrate($context),
        ]);

        $extraMerchantData = $this->getExtraMerchantData($dataBag, $context);

        if (null !== $extraMerchantData->getMerchantData()) {
            $request->assign(['merchantData' => $extraMerchantData->getMerchantData()]);
        }

        if (null !== $extraMerchantData->getAttachment()) {
            $attachment = new Attachment();
            $attachment->assign(['data' => $extraMerchantData->getAttachment()]);

            $request->assign(['attachment' => $attachment]);
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

    private function hydrateOrderLines(Cart $cart, SalesChannelContext $salesChannelContext): array
    {
        $orderLines = [];

        $currency = $salesChannelContext->getCurrency();
        $context  = $salesChannelContext->getContext();

        foreach ($this->lineItemHydrator->hydrate($cart->getLineItems(), $currency, $context) as $orderLine) {
            $orderLines[] = $orderLine;
        }

        foreach ($this->deliveryHydrator->hydrate($cart->getDeliveries(), $currency, $context) as $orderLine) {
            $orderLines[] = $orderLine;
        }

        return array_filter($orderLines);
    }

    private function getMerchantUrls(SyncPaymentTransactionStruct $transaction): array
    {
        $notificationUrl = $this->router->generate(
            'frontend.klarna.callback.notification',
            [
                'transaction_id' => $transaction->getOrderTransaction()->getId(),
            ],
            RouterInterface::ABSOLUTE_URL
        );

        if ($transaction instanceof AsyncPaymentTransactionStruct) {
            /** @var AsyncPaymentTransactionStruct $confirmationUrl */
            $confirmationUrl = $transaction->getReturnUrl();
        } else {
            $confirmationUrl = $this->router->generate('frontend.klarna.instantShopping.finish', [
                'order_id'       => $transaction->getOrder()->getId(),
                'transaction_id' => $transaction->getOrderTransaction()->getId(),
            ],
            RouterInterface::ABSOLUTE_URL);
        }

        return [
            'confirmation' => $confirmationUrl,
            'notification' => $notificationUrl,
        ];
    }

    private function getExtraMerchantData(RequestDataBag $dataBag, SalesChannelContext $context): ExtraMerchantData
    {
        $config            = $this->configReader->read($context->getSalesChannel()->getId());
        $extraMerchantData = new ExtraMerchantData();

        if (!$config->get('kpSendExtraMerchantData')) {
            return $extraMerchantData;
        }

        $customerData = $dataBag->get('klarnaCustomerData');

        if (empty($customerData)) {
            return $extraMerchantData;
        }

        $customerData = json_decode($customerData, true);

        $extraMerchantData->assign([
            'merchantData' => $customerData['merchant_data'],
        ]);

        $attachment = $customerData['attachment'];

        if (!empty($attachment)) {
            $attachment = json_decode($customerData['attachment']['body'], true);

            $extraMerchantData->assign([
                'attachment' => $attachment,
            ]);
        }

        return $extraMerchantData;
    }
}
