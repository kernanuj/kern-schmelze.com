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
use KlarnaPayment\Components\ConfigReader\ConfigReaderInterface;
use KlarnaPayment\Components\Event\SetExtraMerchantDataEvent;
use KlarnaPayment\Components\Extension\ErrorMessageExtension;
use KlarnaPayment\Components\Extension\SessionDataExtension;
use KlarnaPayment\Components\Helper\PaymentHelper\PaymentHelperInterface;
use KlarnaPayment\Components\Struct\ExtraMerchantData;
use KlarnaPayment\Installer\PaymentMethodInstaller;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPage;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

    /** @var CartService */
    private $cartService;

    /** @var CartHasherInterface */
    private $cartHasher;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var ConfigReaderInterface */
    private $configReader;

    public function __construct(
        PaymentHelperInterface $paymentHelper,
        CreateSessionRequestHydratorInterface $requestHydrator,
        AddressStructHydratorInterface $addressHydrator,
        CustomerStructHydratorInterface $customerHydrator,
        ClientInterface $client,
        CartService $cartService,
        CartHasherInterface $cartHasher,
        EventDispatcherInterface $eventDispatcher,
        ConfigReaderInterface $configReader
    ) {
        $this->paymentHelper    = $paymentHelper;
        $this->requestHydrator  = $requestHydrator;
        $this->addressHydrator  = $addressHydrator;
        $this->customerHydrator = $customerHydrator;
        $this->client           = $client;
        $this->cartService      = $cartService;
        $this->cartHasher       = $cartHasher;
        $this->eventDispatcher  = $eventDispatcher;
        $this->configReader     = $configReader;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => 'startKlarnaCheckoutSession',
        ];
    }

    public function startKlarnaCheckoutSession(CheckoutConfirmPageLoadedEvent $event): void
    {
        $context = $event->getSalesChannelContext();

        if (!$this->paymentHelper->isKlarnaPaymentsEnabled($context)) {
            return;
        }

        $cart = $event->getPage()->getCart();

        $response = $this->createKlarnaSession($cart, $context);

        if ($response->getHttpStatus() !== 200) {
            if ($this->paymentHelper->isKlarnaPaymentsSelected($context)) {
                $this->createErrorMessageExtension($event);
            }

            $this->removeSessionDataExtension($cart, $context);
            $this->removeAllKlarnaPaymentMethods($event);

            return;
        }

        $this->createSessionDataExtension($response, $cart, $context);
        $this->removeDisabledKlarnaPaymentMethods($cart, $event);
        $this->filterPayNowMethods($cart, $event->getPage());
    }

    private function filterPayNowMethods(Cart $cart, CheckoutConfirmPage $page): void
    {
        /** @var SessionDataExtension $sessionExtension */
        $sessionExtension = $cart->getExtension(SessionDataExtension::EXTENSION_NAME);
        foreach ($sessionExtension->getPaymentMethodCategories() as $paymentCategory) {
            if ($paymentCategory['identifier'] === PaymentMethodInstaller::KLARNA_PAYMENTS_PAY_NOW_CODE) {
                $this->removeSeparatePayNowKlarnaPaymentMethods($page);

                return;
            }
        }

        $this->removeCombinedKlarnaPaymentPayNowMethod($page);
    }

    private function createErrorMessageExtension(CheckoutConfirmPageLoadedEvent $event): void
    {
        $errorMessageExtension = new ErrorMessageExtension(ErrorMessageExtension::GENERIC_ERROR);

        $event->getPage()->addExtension(ErrorMessageExtension::EXTENSION_NAME, $errorMessageExtension);
    }

    private function createSessionDataExtension(GenericResponse $response, Cart $cart, SalesChannelContext $context): void
    {
        $sessionData = new SessionDataExtension();
        $sessionData->assign([
            'sessionId'                     => $response->getResponse()['session_id'],
            'clientToken'                   => $response->getResponse()['client_token'],
            'paymentMethodCategories'       => $response->getResponse()['payment_method_categories'],
            'selectedPaymentMethodCategory' => $this->getKlarnaCodeFromPaymentMethod($context),
            'cartHash'                      => $this->cartHasher->generate($cart, $context),
        ]);

        if ($this->paymentHelper->isKlarnaPaymentsSelected($context)) {
            $extraMerchantData = $this->getExtraMerchantData($sessionData, $cart, $context);

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

        $cart->addExtension(SessionDataExtension::EXTENSION_NAME, $sessionData);

        $this->cartService->recalculate($cart, $context);
    }

    private function removeSessionDataExtension(Cart $cart, SalesChannelContext $context): void
    {
        if (!$cart->hasExtension(SessionDataExtension::EXTENSION_NAME)) {
            return;
        }

        $cart->removeExtension(SessionDataExtension::EXTENSION_NAME);

        $this->cartService->recalculate($cart, $context);
    }

    private function removeDisabledKlarnaPaymentMethods(Cart $cart, CheckoutConfirmPageLoadedEvent $event): void
    {
        /** @var SessionDataExtension $sessionData */
        $sessionData = $cart->getExtension(SessionDataExtension::EXTENSION_NAME);

        if (empty($sessionData->getPaymentMethodCategories())) {
            return;
        }

        $availablePaymentMethods = array_column($sessionData->getPaymentMethodCategories(), 'identifier');

        $event->getPage()->setPaymentMethods(
            $event->getPage()->getPaymentMethods()->filter(
                static function (PaymentMethodEntity $paymentMethod) use ($availablePaymentMethods) {
                    if (!array_key_exists($paymentMethod->getId(), PaymentMethodInstaller::KLARNA_PAYMENTS_CODES)) {
                        return true;
                    }

                    return in_array(PaymentMethodInstaller::KLARNA_PAYMENTS_CODES[$paymentMethod->getId()], $availablePaymentMethods, true);
                }
            )
        );
    }

    private function removeSeparatePayNowKlarnaPaymentMethods(CheckoutConfirmPage $page): void
    {
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

    private function removeCombinedKlarnaPaymentPayNowMethod(CheckoutConfirmPage $page): void
    {
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

    private function removeAllKlarnaPaymentMethods(CheckoutConfirmPageLoadedEvent $event): void
    {
        $event->getPage()->setPaymentMethods(
            $event->getPage()->getPaymentMethods()->filter(
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

    private function getExtraMerchantData(
        SessionDataExtension $sessionData,
        Cart $cart,
        SalesChannelContext $context
    ): ExtraMerchantData {
        $config = $this->configReader->read($context->getSalesChannel()->getId());
        $data   = new ExtraMerchantData();

        if ($config->get('kpSendExtraMerchantData')) {
            $this->eventDispatcher->dispatch(
                new SetExtraMerchantDataEvent($data, $sessionData, $cart, $context)
            );
        }

        return $data;
    }
}
