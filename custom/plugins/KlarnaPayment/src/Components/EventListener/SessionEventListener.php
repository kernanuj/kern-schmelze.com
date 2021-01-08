<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\EventListener;

use KlarnaPayment\Components\CartHasher\CartHasherInterface;
use KlarnaPayment\Components\Client\ClientInterface;
use KlarnaPayment\Components\Client\Hydrator\Request\CreateSession\CreateSessionRequestHydratorInterface;
use KlarnaPayment\Components\Client\Hydrator\Struct\Address\AddressStructHydrator;
use KlarnaPayment\Components\Client\Hydrator\Struct\Address\AddressStructHydratorInterface;
use KlarnaPayment\Components\Client\Hydrator\Struct\Customer\CustomerStructHydratorInterface;
use KlarnaPayment\Components\Client\Response\GenericResponse;
use KlarnaPayment\Components\Client\Struct\Attachment;
use KlarnaPayment\Components\Extension\ErrorMessageExtension;
use KlarnaPayment\Components\Extension\SessionDataExtension;
use KlarnaPayment\Components\Factory\MerchantDataFactoryInterface;
use KlarnaPayment\Components\Helper\OrderFetcherInterface;
use KlarnaPayment\Components\Helper\PaymentHelper\PaymentHelperInterface;
use KlarnaPayment\Installer\Modules\PaymentMethodInstaller;
use LogicException;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Storefront\Page\Page;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SessionEventListener implements EventSubscriberInterface
{
    /** @var PaymentHelperInterface */
    private $paymentHelper;

    /** @var CreateSessionRequestHydratorInterface */
    private $requestHydrator;

    /** @var AddressStructHydratorInterface */
    private $addressHydrator;

    /** @var CustomerStructHydratorInterface */
    private $customerHydrator;

    /** @var ClientInterface */
    private $client;

    /** @var CartHasherInterface */
    private $cartHasher;

    /** @var MerchantDataFactoryInterface */
    private $merchantDataFactory;

    /** @var OrderConverter */
    private $orderConverter;

    /** @var OrderFetcherInterface */
    private $orderFetcher;

    public function __construct(
        PaymentHelperInterface $paymentHelper,
        CreateSessionRequestHydratorInterface $requestHydrator,
        AddressStructHydratorInterface $addressHydrator,
        CustomerStructHydratorInterface $customerHydrator,
        ClientInterface $client,
        CartHasherInterface $cartHasher,
        MerchantDataFactoryInterface $merchantDataFactory,
        OrderConverter $orderConverter,
        OrderFetcherInterface $orderFetcher
    ) {
        $this->paymentHelper       = $paymentHelper;
        $this->requestHydrator     = $requestHydrator;
        $this->addressHydrator     = $addressHydrator;
        $this->customerHydrator    = $customerHydrator;
        $this->client              = $client;
        $this->cartHasher          = $cartHasher;
        $this->merchantDataFactory = $merchantDataFactory;
        $this->orderConverter      = $orderConverter;
        $this->orderFetcher        = $orderFetcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class  => 'startKlarnaSession',
            AccountEditOrderPageLoadedEvent::class => 'startKlarnaSession',
        ];
    }

    public function startKlarnaSession(PageLoadedEvent $event): void
    {
        $context = $event->getSalesChannelContext();

        if (!$this->paymentHelper->isKlarnaPaymentsEnabled($context)) {
            return;
        }

        if ($event instanceof CheckoutConfirmPageLoadedEvent) {
            $cart = $event->getPage()->getCart();
        } elseif ($event instanceof AccountEditOrderPageLoadedEvent) {
            /** @phpstan-ignore-next-line */
            $cart = $this->convertCartFromOrder($event->getPage()->getOrder(), $event->getContext());
        } else {
            return;
        }

        $response = $this->createKlarnaSession($cart, $context);

        if ($response->getHttpStatus() !== 200) {
            if ($this->paymentHelper->isKlarnaPaymentsSelected($context)) {
                $this->createErrorMessageExtension($event);
            }

            $this->removeAllKlarnaPaymentMethods($event->getPage());

            return;
        }

        $this->createSessionDataExtension($response, $event->getPage(), $cart, $context);

        $this->removeDisabledKlarnaPaymentMethods($event->getPage());
        $this->filterPayNowMethods($event->getPage());
    }

    private function filterPayNowMethods(Struct $page): void
    {
        if (!($page instanceof Page)) {
            return;
        }

        /** @var null|SessionDataExtension $sessionData */
        $sessionData = $page->getExtension(SessionDataExtension::EXTENSION_NAME);

        if (null === $sessionData) {
            return;
        }

        foreach ($sessionData->getPaymentMethodCategories() as $paymentCategory) {
            if ($paymentCategory['identifier'] === PaymentMethodInstaller::KLARNA_PAYMENTS_PAY_NOW_CODE) {
                $this->removeSeparatePayNowKlarnaPaymentMethods($page);

                return;
            }
        }

        $this->removeCombinedKlarnaPaymentPayNowMethod($page);
    }

    private function createErrorMessageExtension(PageLoadedEvent $event): void
    {
        $errorMessageExtension = new ErrorMessageExtension(ErrorMessageExtension::GENERIC_ERROR);

        $event->getPage()->addExtension(ErrorMessageExtension::EXTENSION_NAME, $errorMessageExtension);
    }

    private function createSessionDataExtension(GenericResponse $response, Struct $page, Cart $cart, SalesChannelContext $context): void
    {
        if (!($page instanceof Page)) {
            return;
        }

        $sessionData = new SessionDataExtension();
        $sessionData->assign([
            'sessionId'                     => $response->getResponse()['session_id'],
            'clientToken'                   => $response->getResponse()['client_token'],
            'paymentMethodCategories'       => $response->getResponse()['payment_method_categories'],
            'selectedPaymentMethodCategory' => $this->getKlarnaCodeFromPaymentMethod($context),
            'cartHash'                      => $this->cartHasher->generate($cart, $context),
        ]);

        if ($this->paymentHelper->isKlarnaPaymentsSelected($context)) {
            $extraMerchantData = $this->merchantDataFactory->getExtraMerchantData($sessionData, $cart, $context);

            if (!empty($extraMerchantData->getAttachment())) {
                $attachment = new Attachment();
                $attachment->assign([
                    'data' => $extraMerchantData->getAttachment(),
                ]);
            } else {
                $attachment = null;
            }

            $sessionData->assign([
                'customerData' => [
                    'billing_address'  => $this->addressHydrator->hydrateFromContext($context, AddressStructHydrator::TYPE_BILLING),
                    'shipping_address' => $this->addressHydrator->hydrateFromContext($context, AddressStructHydrator::TYPE_SHIPPING),
                    'customer'         => $this->customerHydrator->hydrate($context),
                    'merchant_data'    => $extraMerchantData->getMerchantData(),
                    'attachment'       => $attachment,
                ],
            ]);
        }

        $page->addExtension(SessionDataExtension::EXTENSION_NAME, $sessionData);
    }

    private function removeDisabledKlarnaPaymentMethods(Struct $page): void
    {
        if (!($page instanceof Page)) {
            return;
        }

        /** @var null|SessionDataExtension $sessionData */
        $sessionData = $page->getExtension(SessionDataExtension::EXTENSION_NAME);

        if (null === $sessionData) {
            return;
        }

        if (empty($sessionData->getPaymentMethodCategories())) {
            return;
        }

        if (!method_exists($page, 'setPaymentMethods') || !method_exists($page, 'getPaymentMethods')) {
            return;
        }

        $availablePaymentMethods = array_column($sessionData->getPaymentMethodCategories(), 'identifier');

        $page->setPaymentMethods(
            $page->getPaymentMethods()->filter(
                static function (PaymentMethodEntity $paymentMethod) use ($availablePaymentMethods) {
                    if (!array_key_exists($paymentMethod->getId(), PaymentMethodInstaller::KLARNA_PAYMENTS_CODES)) {
                        return true;
                    }

                    return in_array(PaymentMethodInstaller::KLARNA_PAYMENTS_CODES[$paymentMethod->getId()], $availablePaymentMethods, true);
                }
            )
        );
    }

    private function removeSeparatePayNowKlarnaPaymentMethods(Page $page): void
    {
        if (!method_exists($page, 'setPaymentMethods') || !method_exists($page, 'getPaymentMethods')) {
            return;
        }

        $page->setPaymentMethods(
            $page->getPaymentMethods()->filter(
                static function (PaymentMethodEntity $paymentMethod) {
                    if (!array_key_exists($paymentMethod->getId(), PaymentMethodInstaller::KLARNA_PAYMENTS_CODES)) {
                        return true;
                    }

                    return in_array($paymentMethod->getId(), PaymentMethodInstaller::KLARNA_PAYMENTS_CODES_WITH_PAY_NOW_COMBINED, true);
                }
            )
        );
    }

    private function removeCombinedKlarnaPaymentPayNowMethod(Page $page): void
    {
        if (!method_exists($page, 'setPaymentMethods') || !method_exists($page, 'getPaymentMethods')) {
            return;
        }

        $page->setPaymentMethods(
            $page->getPaymentMethods()->filter(
                static function (PaymentMethodEntity $paymentMethod) {
                    if (!array_key_exists($paymentMethod->getId(), PaymentMethodInstaller::KLARNA_PAYMENTS_CODES)) {
                        return true;
                    }

                    return $paymentMethod->getId() !== PaymentMethodInstaller::KLARNA_PAY_NOW;
                }
            )
        );
    }

    private function removeAllKlarnaPaymentMethods(Struct $page): void
    {
        if (!($page instanceof Page) || !method_exists($page, 'setPaymentMethods') || !method_exists($page, 'getPaymentMethods')) {
            return;
        }

        $page->setPaymentMethods(
            $page->getPaymentMethods()->filter(
                static function (PaymentMethodEntity $paymentMethod) {
                    if (array_key_exists($paymentMethod->getId(), PaymentMethodInstaller::KLARNA_PAYMENTS_CODES)) {
                        return false;
                    }

                    return true;
                }
            )
        );
    }

    private function createKlarnaSession(Cart $cart, SalesChannelContext $context): GenericResponse
    {
        $request = $this->requestHydrator->hydrate($cart, $context);

        return $this->client->request($request, $context->getContext());
    }

    private function getKlarnaCodeFromPaymentMethod(SalesChannelContext $context): string
    {
        if (!array_key_exists($context->getPaymentMethod()->getId(), PaymentMethodInstaller::KLARNA_PAYMENTS_CODES)) {
            return '';
        }

        return PaymentMethodInstaller::KLARNA_PAYMENTS_CODES[$context->getPaymentMethod()->getId()];
    }

    private function convertCartFromOrder(OrderEntity $orderEntity, Context $context): Cart
    {
        $order = $this->orderFetcher->getOrderFromOrder($orderEntity->getId(), $context);

        if (null === $order) {
            throw new LogicException('could not find order via id');
        }

        return $this->orderConverter->convertToCart($order, $context);
    }
}
