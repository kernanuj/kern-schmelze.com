<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Controller\Storefront;

use Exception;
use KlarnaPayment\Components\CartHasher\CartHasherInterface;
use KlarnaPayment\Components\Client\ClientInterface;
use KlarnaPayment\Components\Client\Hydrator\Struct\Address\AddressStructHydrator;
use KlarnaPayment\Components\Client\Hydrator\Struct\Address\AddressStructHydratorInterface;
use KlarnaPayment\Components\Client\Hydrator\Struct\LineItem\LineItemStructHydratorInterface;
use KlarnaPayment\Components\Client\Hydrator\Struct\ShippingOptions\ShippingOptionsStructHydratorInterface;
use KlarnaPayment\Components\Client\Request\DenyOrderRequest;
use KlarnaPayment\Components\ConfigReader\ConfigReaderInterface;
use KlarnaPayment\Components\Exception\InvalidMerchantData;
use KlarnaPayment\Components\Extension\SessionDataExtension;
use KlarnaPayment\Components\Helper\CurrencyHelper\CurrencyHelperInterface;
use KlarnaPayment\Components\Helper\LocaleHelper\LocaleHelperInterface;
use KlarnaPayment\Components\Helper\MerchantUrlHelper\MerchantUrlHelperInterface;
use KlarnaPayment\Components\Helper\SalesChannelHelper\SalesChannelHelperInterface;
use KlarnaPayment\Components\Helper\ShippingMethodHelper\ShippingMethodHelperInterface;
use KlarnaPayment\Components\InstantShopping\CartHandler\CartHandlerInterface;
use KlarnaPayment\Components\InstantShopping\ContextHandler\ContextHandlerInterface;
use KlarnaPayment\Components\InstantShopping\CustomerHandler\CustomerHandler;
use KlarnaPayment\Components\InstantShopping\CustomerHandler\CustomerHandlerInterface;
use KlarnaPayment\Components\InstantShopping\DataProvider\PlaceOrderCallbackProviderInterface;
use KlarnaPayment\Components\InstantShopping\DataProvider\UpdateCallbackProviderInterface;
use KlarnaPayment\Components\InstantShopping\DataProvider\UpdateDataProviderInterface;
use KlarnaPayment\Components\InstantShopping\MerchantDataProvider\MerchantDataProviderInterface;
use KlarnaPayment\Installer\Modules\RuleInstaller;
use LogicException;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\SalesChannelRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 *
 * The entirety of Instant Shopping callbacks (browser-side and server-side) needs to use simulated Context objects with the correct customer being set. Make sure to simulate a context before loading a cart, otherwise the cart's customer is emptied/changed.
 */
class InstantShoppingController extends StorefrontController
{
    /** @var UpdateDataProviderInterface */
    protected $updateDataProvider;

    /** @var UpdateCallbackProviderInterface */
    protected $updateCallbackProvider;

    /** @var AddressStructHydratorInterface */
    private $addressHydrator;

    /** @var ConfigReaderInterface */
    private $configReader;

    /** @var LoggerInterface */
    private $logger;

    /** @var PlaceOrderCallbackProviderInterface */
    private $placeOrderCallbackProvider;

    /** @var ClientInterface */
    private $client;

    /** @var ContextHandlerInterface */
    private $contextHandler;

    /** @var CartHandlerInterface */
    private $cartHandler;

    /** @var CustomerHandlerInterface */
    private $customerHandler;

    /** @var MerchantDataProviderInterface */
    private $merchantDataProvider;

    /** @var LineItemStructHydratorInterface */
    private $lineItemHydrator;

    /** @var ShippingOptionsStructHydratorInterface */
    private $shippingOptionsHydrator;

    /** @var CartHasherInterface */
    private $cartHasher;

    /** @var CurrencyHelperInterface */
    private $currencyHelper;

    /** @var MerchantUrlHelperInterface */
    private $merchantUrlHelper;

    /** @var CartService */
    private $cartService;

    /** @var LocaleHelperInterface */
    private $localeHelper;

    /** @var ShippingMethodHelperInterface */
    private $shippingMethodHelper;

    /** @var SalesChannelHelperInterface */
    private $salesChannelHelper;

    public function __construct(
        UpdateDataProviderInterface $updateDataProvider,
        UpdateCallbackProviderInterface $updateCallbackProvider,
        PlaceOrderCallbackProviderInterface $placeOrderCallbackProvider,
        AddressStructHydratorInterface $addressHydrator,
        ConfigReaderInterface $configReader,
        LoggerInterface $logger,
        ClientInterface $client,
        ContextHandlerInterface $contextHandler,
        CartHandlerInterface $cartHandler,
        CustomerHandlerInterface $customerHandler,
        MerchantDataProviderInterface $merchantDataProvider,
        LineItemStructHydratorInterface $lineItemHydrator,
        ShippingOptionsStructHydratorInterface $shippingOptionsHydrator,
        CartHasherInterface $cartHasher,
        CurrencyHelperInterface $currencyHelper,
        MerchantUrlHelperInterface $merchantUrlHelper,
        CartService $cartService,
        LocaleHelperInterface $localeHelper,
        ShippingMethodHelperInterface $shippingMethodHelper,
        SalesChannelHelperInterface $salesChannelHelper
    ) {
        $this->updateDataProvider         = $updateDataProvider;
        $this->updateCallbackProvider     = $updateCallbackProvider;
        $this->placeOrderCallbackProvider = $placeOrderCallbackProvider;
        $this->addressHydrator            = $addressHydrator;
        $this->configReader               = $configReader;
        $this->logger                     = $logger;
        $this->client                     = $client;
        $this->contextHandler             = $contextHandler;
        $this->cartHandler                = $cartHandler;
        $this->customerHandler            = $customerHandler;
        $this->merchantDataProvider       = $merchantDataProvider;
        $this->lineItemHydrator           = $lineItemHydrator;
        $this->shippingOptionsHydrator    = $shippingOptionsHydrator;
        $this->cartHasher                 = $cartHasher;
        $this->currencyHelper             = $currencyHelper;
        $this->merchantUrlHelper          = $merchantUrlHelper;
        $this->cartService                = $cartService;
        $this->localeHelper               = $localeHelper;
        $this->shippingMethodHelper       = $shippingMethodHelper;
        $this->salesChannelHelper         = $salesChannelHelper;
    }

    /**
     * @Route("/klarna-instant-shopping/finish/{order_id}/{transaction_id}", defaults={"csrf_protected": false}, name="frontend.klarna.instantShopping.finish", methods={"GET"})
     */
    public function loginAfterSuccess(Request $request, SalesChannelContext $context): Response
    {
        $orderId       = $request->get('order_id');
        $transactionId = $request->get('transaction_id');

        /** @var OrderCustomerEntity $customer */
        $customer = $this->placeOrderCallbackProvider->getCustomerByOrderAndTransactionId($orderId, $transactionId, $context);
        $this->placeOrderCallbackProvider->emptyCart($context);
        $customerEntity = $customer->getCustomer();

        if ($customerEntity) {
            $this->placeOrderCallbackProvider->loginUser($customerEntity, $context);
        }

        return new RedirectResponse($this->placeOrderCallbackProvider->getFinishUrl($orderId));
    }

    /**
     * @Route("/klarna-instant-shopping/place-order", defaults={"csrf_protected": false}, name="frontend.klarna.instantShopping.placeOrder", methods={"POST"})
     */
    public function placeOrder(Request $request, SalesChannelContext $context): Response
    {
        if (!$this->isInstantShoppingEnabled($context)) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        $denyOrderRequest = new DenyOrderRequest();

        try {
            $denyOrderRequest->assign(['authorizationToken' => $request->get('authorization_token')]);
            $orderData = new RequestDataBag($request->get('order'));
            $orderData->set('klarnaAuthorizationToken', $request->get('authorization_token'));

            $merchantData = $this->extractMerchantData($request);

            $customerId = $this->cartHandler->getCustomerIdFromCartToken($merchantData['klarna_cart_token']);

            if (!$customerId) {
                $context = $this->getGuestContext($context, false);

                if (!($context->getCustomer() instanceof CustomerEntity)) {
                    $this->logger->error('Missing customer for cart token for instant shopping place order callback');

                    return new JsonResponse('', Response::HTTP_BAD_REQUEST);
                }

                $customerId = $context->getCustomer()->getId();
            }

            $currencyIso = $request->get('order', [])['purchase_currency'] ?? '';
            $shippingId  = $request->get('order', [])['selected_shipping_option']['id'];

            if (!$this->shippingMethodHelper->shippingMethodIdExists($shippingId, $context)) {
                $this->customerHandler->cleanupGuestCustomer($context->getCustomer(), null, $context->getContext());
                throw new LogicException(sprintf('Invalid shipping method ID %s on place order', $shippingId));
            }

            $context = $this->contextHandler->createSalesChannelContext(
                Random::getAlphanumericString(32),
                $customerId,
                $this->currencyHelper->getCurrencyIdFromIso($currencyIso, $context->getContext()),
                $shippingId,
                $context
            );

            // Reload cart with correct customer context
            $cart = $this->cartHandler->getInstantShoppingCartByToken($merchantData['klarna_cart_token'], $context);
            $this->cartService->recalculate($cart, $context);
            $data = $this->placeOrderCallbackProvider->updateCustomer($cart, $request, $context);
            $cart = $this->placeOrderCallbackProvider->getUpdatedCart($cart, $merchantData['klarna_cart_hash'], $data['context']);

            $this->placeOrderCallbackProvider->createOrder($cart, $request, $orderData, $data['context']);
            $this->placeOrderCallbackProvider->deleteTemporaryKlarnaAddresses($data['context']);

            //TODO: shopware issue NEXT-7965, resetting addresses will result in irritating wrong output on checkout finish
            //$this->placeOrderCallbackProvider->resetDefaultAddresses($data);
        } catch (Exception $e) {
            if ($this->isDebugMode($context)) {
                $this->logger->debug('Error on order creation', ['exception' => $e]);
            }

            $denyOrderRequest->assign(['isError' => true, 'deny_message' => $e->getMessage()]);
        }

        if ($denyOrderRequest->isError() === true) {
            $this->client->request($denyOrderRequest, $context->getContext());

            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse('', Response::HTTP_OK);
    }

    /**
     * @Route("/klarna-instant-shopping/update", defaults={"csrf_protected": false, "XmlHttpRequest": true}, name="frontend.klarna.instantShopping.update", methods={"POST"})
     */
    public function updateCallback(Request $request, SalesChannelContext $context): Response
    {
        if (!$this->isInstantShoppingEnabled($context)) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        if ($this->updateCallbackProvider->isUpdateRequired($request) === false) {
            return new Response('', Response::HTTP_NOT_MODIFIED);
        }

        $merchantData = $this->extractMerchantData($request);

        if (!array_key_exists('klarna_cart_token', $merchantData) || empty($merchantData['klarna_cart_token'])) {
            // Initial call before cart is created
            return new Response('', Response::HTTP_NOT_MODIFIED);
        }

        $customerId = $this->cartHandler->getCustomerIdFromCartToken($merchantData['klarna_cart_token']);

        if (!$customerId) {
            $context = $this->getGuestContext($context);

            if (!($context->getCustomer() instanceof CustomerEntity)) {
                $this->logger->error('Missing customer for cart token for instant shopping update callback');

                return new JsonResponse('', Response::HTTP_BAD_REQUEST);
            }

            $customerId = $context->getCustomer()->getId();
        }

        $context = $this->contextHandler->createSalesChannelContext(
            Random::getAlphanumericString(32),
            $customerId,
            $this->currencyHelper->getCurrencyIdFromIso($request->get('purchase_currency', $context->getCurrency()->getIsoCode()), $context->getContext()),
            null,
            $context
        );
        $customer = $context->getCustomer();

        if (!($customer instanceof CustomerEntity)) {
            $this->logger->error('Missing customer for instant shopping update callback');

            return new JsonResponse('', Response::HTTP_BAD_REQUEST);
        }

        $currencyIso = $request->get('purchase_currency', '');
        $shippingId  = $request->get('selected_shipping_option') ? $request->get('selected_shipping_option')['id'] : null;

        if (!$this->shippingMethodHelper->shippingMethodIdExists($shippingId, $context)) {
            $shippingId = null;
        }

        $defaultBillingAddressId  = $customer->getDefaultBillingAddressId();
        $defaultShippingAddressId = $customer->getDefaultShippingAddressId();

        $addressData = $this->customerHandler->updateContextCustomerAddresses($customer, $request->get('billing_address'), $request->get('shipping_address'), $context->getCurrency()->getIsoCode(), $context);
        $context     = $addressData['context'];

        $context = $this->contextHandler->createSalesChannelContext(
            $context->getToken(),
            $context->getCustomer()->getId(),
            $this->currencyHelper->getCurrencyIdFromIso($currencyIso, $context->getContext()),
            $shippingId,
            $context
        );

        // Reload cart with correct customer context
        $cart = $this->cartHandler->getInstantShoppingCartByToken($merchantData['klarna_cart_token'], $context);
        $cart = $this->updateCallbackProvider->updateBasketPositions($cart, $request, $context);
        $cart = $this->updateCallbackProvider->recalculateShippingCosts($cart, $request, $context);

        $sessionData       = $this->prepareInstantShoppingAdditionalSessionData($cart, $context);
        $extraMerchantData = $this->merchantDataProvider->getExtraMerchantData($sessionData, $cart, $context);

        $billingAddress = $this->addressHydrator->hydrateFromContext($context, AddressStructHydrator::TYPE_BILLING);

        $response = [
            'order_lines'        => array_filter($this->lineItemHydrator->hydrate($cart->getLineItems(), $context->getCurrency(), $context->getContext())),
            'shipping_options'   => array_filter($this->shippingOptionsHydrator->hydrate($cart, $context)),
            'merchant_data'      => $extraMerchantData->getMerchantData(),
            'merchant_urls'      => $this->merchantUrlHelper->getMerchantUrls($this->merchantUrlHelper->getSalesChannelDomainFromRequest($request, $context->getContext())),
            'shipping_countries' => $this->getCountries($context->getSalesChannel()->getId(), $context->getContext()),
        ];

        if ($billingAddress) {
            $response['purchase_country'] = $billingAddress->getCountry();
        } elseif ($request->get('billing_address') && $request->get('billing_address', ['country' => null])['country']) {
            $response['purchase_country'] = $request->get('billing_address')['country'];
        } elseif ($request->get('purchase_country')) {
            $response['purchase_country'] = $request->get('purchase_country');
        }

        if (array_key_exists('purchase_country', $response)) {
            $response['locale'] = $this->localeHelper->getKlarnaLocale(
                (string) $request->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_LOCALE, 'en-GB'),
                $response['purchase_country']
            );
        }

        $this->customerHandler->setDefaultBillingAddress($defaultBillingAddressId, $customer, $addressData['context']);
        $this->customerHandler->setDefaultShippingAddress($defaultShippingAddressId, $customer, $addressData['context']);

        $this->customerHandler->deleteTemporaryAddresses($addressData, $addressData['context']);
        $this->customerHandler->cleanupGuestCustomer($customer, $cart->getToken(), $context->getContext());

        return new JsonResponse($response);
    }

    /**
     * @Route("/klarna-instant-shopping/update-data", defaults={"csrf_protected": false, "XmlHttpRequest": true}, name="frontend.klarna.instantShopping.updateData", methods={"POST"})
     */
    public function updateData(Request $request, SalesChannelContext $context): JsonResponse
    {
        if (!$this->isInstantShoppingEnabled($context)) {
            if ($this->isDebugMode($context)) {
                $this->logger->debug('update-data callback for instant shopping received although it\'s disabled');
            }

            return new JsonResponse(['status' => 'error'], Response::HTTP_BAD_REQUEST);
        }

        $productId = (string) $request->get('productId', '');

        if (empty($productId)) {
            $cart = $this->updateDataProvider->getInstantShoppingCart($context);
        } else {
            $cart = $this->updateDataProvider->createInstantShoppingCart($productId, (int) $request->get('productQuantity', 1), $context);
        }

        if (!$context->getCustomer()) {
            $context = $this->getGuestContext($context);
        }

        //return error if cart or positions are not set
        if ($cart === null || $cart->getLineItems()->count() < 1) {
            $this->logger->error('Missing cart or no line items for instant shopping update call');

            return new JsonResponse(['status' => 'error'], Response::HTTP_BAD_REQUEST);
        }

        $cart->setToken($context->getToken());
        $cart = $this->cartService->recalculate($cart, $context);

        $sessionData       = $this->prepareInstantShoppingAdditionalSessionData($cart, $context);
        $extraMerchantData = $this->merchantDataProvider->getExtraMerchantData($sessionData, $cart, $context);

        $response = [
            'order_lines'      => $this->lineItemHydrator->hydrate($cart->getLineItems(), $context->getCurrency(), $context->getContext()),
            'attachment'       => $this->updateDataProvider->buildAttachment($extraMerchantData),
            'merchant_data'    => $extraMerchantData->getMerchantData(),
            'merchant_urls'    => $this->merchantUrlHelper->getMerchantUrls($this->merchantUrlHelper->getSalesChannelDomainFromRequest($request, $context->getContext())),
            'shipping_options' => array_filter($this->shippingOptionsHydrator->hydrate($cart, $context)),
        ];

        if ($context->getCustomer() !== null && !$context->getCustomer()->getGuest()) {
            $billingAddress = $this->addressHydrator->hydrateFromContext($context, AddressStructHydrator::TYPE_BILLING);

            if ($billingAddress) {
                $response['billing_address'] = $billingAddress;
            }
        }

        $this->customerHandler->cleanupGuestCustomer($context->getCustomer(), $cart->getToken(), $context->getContext());

        return new JsonResponse($response);
    }

    /**
     * @Route("/klarna-instant-shopping/update-identification", defaults={"csrf_protected": false, "XmlHttpRequest": true}, name="frontend.klarna.instantShopping.updateIdentification", methods={"POST"})
     */
    public function updateIdentification(Request $request, SalesChannelContext $context): JsonResponse
    {
        if (!$this->isInstantShoppingEnabled($context)) {
            return new JsonResponse(['status' => 'error'], Response::HTTP_BAD_REQUEST);
        }

        $customerId = $this->cartHandler->getCustomerIdFromCartToken($request->get('cartToken', ''));

        if (empty($customerId)) {
            $context = $this->getGuestContext($context);

            if (!($context->getCustomer() instanceof CustomerEntity)) {
                $this->logger->error('Missing customer for cart token for instant shopping update identification call');

                return new JsonResponse(['status' => 'error'], Response::HTTP_BAD_REQUEST);
            }

            $customerId = $context->getCustomer()->getId();
        }

        $context = $this->contextHandler->createSalesChannelContext(
            Random::getAlphanumericString(32),
            $customerId,
            $this->currencyHelper->getCurrencyIdFromIso($request->get('purchase_currency', $context->getCurrency()->getIsoCode()), $context->getContext()),
            null,
            $context
        );
        $customer = $context->getCustomer();

        if (!$customer) {
            $this->logger->error('Missing customer for instant shopping update identification call');

            return new JsonResponse(['status' => 'error'], Response::HTTP_BAD_REQUEST);
        }

        $cart = $this->cartHandler->getInstantShoppingCartByToken($request->get('cartToken'), $context);

        $address                  = ['country' => $request->get('country'), 'postal_code' => $request->get('postal_code', CustomerHandler::TEMPORARY_ZIP)];
        $defaultBillingAddressId  = $customer->getDefaultBillingAddressId();
        $defaultShippingAddressId = $customer->getDefaultShippingAddressId();

        $addressData = $this->customerHandler->updateContextCustomerAddresses($customer, $address, $address, $context->getCurrency()->getIsoCode(), $context);
        $context     = $addressData['context'];

        $response = [
            'purchase_country' => $request->get('country'),
            'merchant_urls'    => $this->merchantUrlHelper->getMerchantUrls($this->merchantUrlHelper->getSalesChannelDomainFromRequest($request, $context->getContext())),
            'shipping_options' => array_filter($this->shippingOptionsHydrator->hydrate($cart, $context)),
        ];

        if (empty($response['purchase_country'])) {
            $this->customerHandler->cleanupGuestCustomer($customer, $cart->getToken(), $context->getContext());
            throw new LogicException('No country given');
        }

        if (array_key_exists('purchase_country', $response)) {
            $response['locale'] = $this->localeHelper->getKlarnaLocale(
                (string) $request->attributes->get(SalesChannelRequest::ATTRIBUTE_DOMAIN_LOCALE, 'en-GB'),
                $response['purchase_country']
            );
        }

        $this->customerHandler->setDefaultBillingAddress($defaultBillingAddressId, $customer, $addressData['context']);
        $this->customerHandler->setDefaultShippingAddress($defaultShippingAddressId, $customer, $addressData['context']);

        $this->customerHandler->deleteTemporaryAddresses($addressData, $addressData['context']);
        $this->customerHandler->cleanupGuestCustomer($customer, $cart->getToken(), $context->getContext());

        return new JsonResponse($response);
    }

    /**
     * @Route("/klarna-instant-shopping/update-shipping", defaults={"csrf_protected": false, "XmlHttpRequest": true}, name="frontend.klarna.instantShopping.updateShipping", methods={"POST"})
     */
    public function updateShipping(Request $request, SalesChannelContext $context): JsonResponse
    {
        if (!$this->isInstantShoppingEnabled($context)) {
            if ($this->isDebugMode($context)) {
                $this->logger->debug('update-shipping callback for instant shopping received although it\'s disabled');
            }

            return new JsonResponse(['status' => 'error'], Response::HTTP_BAD_REQUEST);
        }

        $customerId = $this->cartHandler->getCustomerIdFromCartToken($request->get('cartToken'));

        if (!$customerId) {
            $context = $this->getGuestContext($context);

            if (!($context->getCustomer() instanceof CustomerEntity)) {
                $this->logger->error('Missing customer for cart token for instant shopping update shipping call');

                return new JsonResponse(['status' => 'error'], Response::HTTP_BAD_REQUEST);
            }

            $customerId = $context->getCustomer()->getId();
        }

        $context = $this->contextHandler->createSalesChannelContext(
            Random::getAlphanumericString(32),
            $customerId,
            $this->currencyHelper->getCurrencyIdFromIso($request->get('purchase_currency', $context->getCurrency()->getIsoCode()), $context->getContext()),
            null,
            $context
        );
        $customer = $context->getCustomer();

        if (!$customer) {
            $this->logger->error('Missing customer for instant shopping update shipping call');

            return new JsonResponse(['status' => 'error'], Response::HTTP_BAD_REQUEST);
        }

        $cart = $this->cartHandler->getInstantShoppingCartByToken($request->get('cartToken'), $context);

        //return error if cart or positions are not set
        if ($cart->getLineItems()->count() < 1) {
            $this->logger->error('Missing cart or no line items for instant shopping update shipping call');
            $this->customerHandler->cleanupGuestCustomer($customer, $cart->getToken(), $context->getContext());

            return new JsonResponse(['status' => 'error'], Response::HTTP_BAD_REQUEST);
        }

        $address                  = ['country' => $request->get('country'), 'postal_code' => $request->get('postal_code', CustomerHandler::TEMPORARY_ZIP)];
        $defaultBillingAddressId  = $customer->getDefaultBillingAddressId();
        $defaultShippingAddressId = $customer->getDefaultShippingAddressId();

        $addressData = $this->customerHandler->updateContextCustomerAddresses($customer, $address, $address, $context->getCurrency()->getIsoCode(), $context);
        $context     = $addressData['context'];

        $context = $this->contextHandler->createSalesChannelContext(
            $context->getToken(),
            $customer->getId(),
            $context->getCurrency()->getId(),
            $request->get('shippingMethodId', null),
            $context
        );
        $this->cartService->recalculate($cart, $context);

        $sessionData       = $this->prepareInstantShoppingAdditionalSessionData($cart, $context);
        $extraMerchantData = $this->merchantDataProvider->getExtraMerchantData($sessionData, $cart, $context);

        $response = [
            'order_lines'      => $this->lineItemHydrator->hydrate($cart->getLineItems(), $context->getCurrency(), $context->getContext()),
            'shipping_options' => array_filter($this->shippingOptionsHydrator->hydrate($cart, $context)),
            'attachment'       => $this->updateDataProvider->buildAttachment($extraMerchantData),
            'merchant_data'    => $extraMerchantData->getMerchantData(),
            'merchant_urls'    => $this->merchantUrlHelper->getMerchantUrls($this->merchantUrlHelper->getSalesChannelDomainFromRequest($request, $context->getContext())),
        ];

        $this->customerHandler->setDefaultBillingAddress($defaultBillingAddressId, $customer, $addressData['context']);
        $this->customerHandler->setDefaultShippingAddress($defaultShippingAddressId, $customer, $addressData['context']);

        $this->customerHandler->deleteTemporaryAddresses($addressData, $addressData['context']);
        $this->customerHandler->cleanupGuestCustomer($customer, $cart->getToken(), $context->getContext());

        return new JsonResponse($response);
    }

    /**
     * @throws InvalidMerchantData
     */
    private function extractMerchantData(Request $request): array
    {
        if ($request->get('order')) {
            $merchantData = json_decode($request->get('order', [])['merchant_data'], true);
        } else {
            $merchantData = json_decode($request->get('merchant_data'), true);
        }

        if (empty($merchantData['klarna_cart_token']) || empty($merchantData['klarna_cart_hash'])) {
            throw new InvalidMerchantData('Klarna cart token or hash missing');
        }

        return $merchantData;
    }

    private function prepareInstantShoppingAdditionalSessionData(Cart $cart, SalesChannelContext $context): SessionDataExtension
    {
        $sessionData = new SessionDataExtension();
        $sessionData->assign([
            'klarnaCartHash'  => $this->cartHasher->generate($cart, $context),
            'klarnaCartToken' => $cart->getToken(),
        ]);

        return $sessionData;
    }

    private function isInstantShoppingEnabled(SalesChannelContext $context): bool
    {
        $pluginConfig = $this->configReader->read($context->getSalesChannel()->getId());

        return $pluginConfig->get('instantShoppingEnabled') === true;
    }

    private function isDebugMode(SalesChannelContext $context): bool
    {
        $pluginConfig = $this->configReader->read($context->getSalesChannel()->getId());

        return $pluginConfig->get('debugMode') === true;
    }

    private function getGuestContext(SalesChannelContext $context, bool $skipCustomerNumber = true): SalesChannelContext
    {
        $address = array_filter(['country' => $context->getShippingLocation()->getCountry()->getIso(), 'postal_code' => CustomerHandler::TEMPORARY_ZIP]);

        return $this->contextHandler->createSalesChannelContext(
            Random::getAlphanumericString(32),
            $this->customerHandler->createGuestCustomer($address, $address, $context, null, true, $skipCustomerNumber)->getId(),
            null,
            null,
            $context
        );
    }

    private function getCountries(string $salesChannelId, Context $context): array
    {
        $salesChannel = $this->salesChannelHelper->getSalesChannel($salesChannelId, $context);
        $countries    = [];

        $salesChannelCountries = $salesChannel->getCountries() !== null ? $salesChannel->getCountries()->getElements() : [];

        foreach ($salesChannelCountries as $country) {
            if (!in_array($country->getIso(), array_keys(RuleInstaller::AVAIBILITY_CONDITIONS), true)) {
                continue;
            }

            $countries[] = $country->getIso();
        }

        return array_filter($countries);
    }
}
