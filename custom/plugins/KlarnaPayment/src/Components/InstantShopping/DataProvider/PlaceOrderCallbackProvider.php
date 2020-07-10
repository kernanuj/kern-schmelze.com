<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\DataProvider;

use KlarnaPayment\Components\CartHasher\CartHasherInterface;
use KlarnaPayment\Components\CartHasher\Exception\InvalidCartHashException;
use KlarnaPayment\Components\Helper\CurrencyHelper\CurrencyHelperInterface;
use KlarnaPayment\Components\InstantShopping\CartHandler\CartHandlerInterface;
use KlarnaPayment\Components\InstantShopping\ContextHandler\ContextHandlerInterface;
use KlarnaPayment\Components\InstantShopping\CustomerHandler\CustomerHandler;
use KlarnaPayment\Components\InstantShopping\CustomerHandler\CustomerHandlerInterface;
use KlarnaPayment\Components\InstantShopping\OrderHandler\OrderHandlerInterface;
use KlarnaPayment\Installer\PaymentMethodInstaller;
use LogicException;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Customer\Event\CustomerBeforeLoginEvent;
use Shopware\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextPersister;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class PlaceOrderCallbackProvider implements PlaceOrderCallbackProviderInterface
{
    /** @var CartHandlerInterface */
    private $cartHandler;

    /** @var CustomerHandlerInterface */
    private $customerHandler;

    /** @var OrderHandlerInterface */
    private $orderHandler;

    /** @var CartService */
    private $cartService;

    /** @var RouterInterface */
    private $router;

    /** @var ContextHandlerInterface */
    private $contextHandler;

    /** @var CartHasherInterface */
    private $cartHasher;

    /** @var SalesChannelContextPersister */
    private $contextPersister;

    /** @var CurrencyHelperInterface */
    private $currencyHelper;

    /** @var EntityRepositoryInterface */
    private $addressRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        CartHandlerInterface $cartHandler,
        CustomerHandlerInterface $customerHandler,
        OrderHandlerInterface $orderHandler,
        CartService $cartService,
        RouterInterface $router,
        ContextHandlerInterface $contextHandler,
        CartHasherInterface $cartHasher,
        SalesChannelContextPersister $contextPersister,
        CurrencyHelperInterface $currencyHelper,
        EntityRepositoryInterface $addressRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->cartHandler       = $cartHandler;
        $this->customerHandler   = $customerHandler;
        $this->orderHandler      = $orderHandler;
        $this->cartService       = $cartService;
        $this->router            = $router;
        $this->contextHandler    = $contextHandler;
        $this->cartHasher        = $cartHasher;
        $this->contextPersister  = $contextPersister;
        $this->currencyHelper    = $currencyHelper;
        $this->addressRepository = $addressRepository;
        $this->eventDispatcher   = $eventDispatcher;
    }

    public function getCustomerByOrderAndTransactionId(string $orderId, string $transactionId, SalesChannelContext $context): ?OrderCustomerEntity
    {
        $order = $this->orderHandler->getOrderByOrderAndTransactionId($orderId, $transactionId, $context);

        return $order->getOrderCustomer();
    }

    public function emptyCart(SalesChannelContext $context): void
    {
        $cart = $this->cartService->getCart($context->getToken(), $context);

        foreach ($cart->getLineItems() as $lineItem) {
            $this->cartService->remove($cart, $lineItem->getId(), $context);
        }
    }

    public function getFinishUrl(string $orderId): string
    {
        return $this->router->generate('frontend.checkout.finish.page', [
            'orderId' => $orderId,
        ],
            UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function loginUser(CustomerEntity $customer, SalesChannelContext $context): void
    {
        $event = new CustomerBeforeLoginEvent($context, $customer->getEmail());
        $this->eventDispatcher->dispatch($event);

        $newToken = $this->contextPersister->replace($context->getToken());
        $this->contextPersister->save(
            $newToken,
            [
                'customerId'        => $customer->getId(),
                'billingAddressId'  => null,
                'shippingAddressId' => null,
            ]
        );

        $event = new CustomerLoginEvent($context, $customer, $newToken);
        $this->eventDispatcher->dispatch($event);
    }

    public function getUpdatedCart(Cart $cart, string $cartHash, SalesChannelContext $context): Cart
    {
        $cart = $this->cartService->recalculate($cart, $context);

        if ($this->cartHasher->validate($cart, $cartHash, $context) === false) {
            throw new InvalidCartHashException('Invalid cart hash');
        }

        return $cart;
    }

    /**
     * $data = [
     *  'context' => SalesChannelContext,
     *  'customer' => CustomerEntity,
     *  'defaultShippingAddressId' => string,
     *  'defaultBillingAddressId' => string
     * ]
     */
    public function resetDefaultAddresses(array $data): void
    {
        if (!empty($data['defaultBillingAddressId']) && !empty($data['defaultShippingAddressId'])) {
            $this->customerHandler->setDefaultShippingAddress($data['defaultShippingAddressId'], $data['customer'], $data['context']);
            $this->customerHandler->setDefaultBillingAddress($data['defaultBillingAddressId'], $data['customer'], $data['context']);
        }
    }

    public function updateCustomer(Cart $cart, Request $request, SalesChannelContext $context): array
    {
        $customer = $this->cartHandler->getCustomerFromCart($cart, $context);

        if ($customer) {
            $defaultShippingAddressId = $customer->getDefaultShippingAddressId();
            $defaultBillingAddressId  = $customer->getDefaultBillingAddressId();

            $customer = $this->updateContextCustomerAddresses($customer, $request, $context);
        } else {
            $customer = $this->customerHandler->createGuestCustomer($request->get('order')['billing_address'], $request->get('order')['shipping_address'], $context, $request->get('order')['customer'], false, false);
        }

        $this->customerHandler->setCustomerPaymentMethod(PaymentMethodInstaller::KLARNA_INSTANT_SHOPPING, $customer, $context);
        $newToken = $this->contextPersister->replace($context->getToken());

        return [
            'context' => $this->contextHandler->createSalesChannelContext(
                $newToken,
                $customer->getId(),
                $this->currencyHelper->getCurrencyIdFromIso($request->get('purchase_currency', ''), $context->getContext()),
                null,
                $context
            ),
            'customer'                 => $customer,
            'defaultShippingAddressId' => $defaultShippingAddressId ?? '',
            'defaultBillingAddressId'  => $defaultBillingAddressId ?? '',
        ];
    }

    public function createOrder(Cart $cart, Request $request, RequestDataBag $dataBag, SalesChannelContext $context): void
    {
        $this->orderHandler->createOrder($cart, $request, $dataBag, $context);
    }

    public function deleteTemporaryKlarnaAddresses(SalesChannelContext $context): void
    {
        $customer = $context->getCustomer();

        if (!$customer) {
            throw new LogicException('Missing customer during deletion of temporary addresses');
        }

        $criteria = new Criteria();
        $criteria
            ->addFilter(new EqualsFilter('customerId', $customer->getId()))
            ->addFilter(new EqualsFilter('firstName', CustomerHandler::KLARNA_INSTANT_SHOPPING_TEMPORARY_IDENTIFYING_VALUE));
        $addressIds = $this->addressRepository->searchIds($criteria, $context->getContext())->getIds();

        if (count($addressIds) > 0) {
            $this->addressRepository->delete(array_map(function ($id) { return ['id' => $id]; }, $addressIds), $context->getContext());
        }
    }

    private function updateContextCustomerAddresses(CustomerEntity $customer, Request $request, SalesChannelContext $context): CustomerEntity
    {
        $billingAddressId  = $this->customerHandler->createCustomerAddress($context, $request->get('order')['billing_address'], $customer->getId(), false, $request->get('order')['customer']);
        $shippingAddressId = $this->customerHandler->createCustomerAddress($context, $request->get('order')['shipping_address'], $customer->getId(), false, $request->get('order')['customer']);

        $this->customerHandler->updateTemporaryCustomerAccount($customer, $request, $context);
        $this->customerHandler->setDefaultShippingAddress($shippingAddressId, $customer, $context);
        $this->customerHandler->setDefaultBillingAddress($billingAddressId, $customer, $context);

        return $customer;
    }
}
