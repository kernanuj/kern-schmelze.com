<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\PaymentHandler;

use KlarnaPayment\Components\Client\ClientInterface;
use KlarnaPayment\Components\Client\Hydrator\Request\CreateOrder\CreateOrderRequestHydratorInterface;
use KlarnaPayment\Components\Client\Hydrator\Request\UpdateAddress\UpdateAddressRequestHydratorInterface;
use KlarnaPayment\Components\Client\Hydrator\Request\UpdateOrder\UpdateOrderRequestHydratorInterface;
use KlarnaPayment\Components\Client\Response\GenericResponse;
use KlarnaPayment\Components\Helper\OrderFetcherInterface;
use KlarnaPayment\Components\Helper\RequestHasherInterface;
use KlarnaPayment\Core\Framework\ContextScope;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AsynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Exception\AsyncPaymentFinalizeException;
use Shopware\Core\Checkout\Payment\Exception\AsyncPaymentProcessException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class KlarnaPaymentsPaymentHandler implements AsynchronousPaymentHandlerInterface
{
    /** @var CreateOrderRequestHydratorInterface */
    private $requestHydrator;

    /** @var ClientInterface */
    private $client;

    /** @var EntityRepositoryInterface */
    private $transactionRepository;

    /** @var EntityRepositoryInterface */
    private $orderRepository;

    /** @var TranslatorInterface */
    private $translator;

    /** @var RequestHasherInterface */
    private $requestHasher;

    /** @var UpdateAddressRequestHydratorInterface */
    private $addressRequestHydrator;

    /** @var UpdateOrderRequestHydratorInterface */
    private $orderRequestHydrator;

    /** @var OrderFetcherInterface */
    private $orderFetcher;

    public function __construct(
        CreateOrderRequestHydratorInterface $requestHydrator,
        ClientInterface $client,
        EntityRepositoryInterface $transactionRepository,
        EntityRepositoryInterface $orderRepository,
        TranslatorInterface $translator,
        RequestHasherInterface $requestHasher,
        UpdateAddressRequestHydratorInterface $addressRequestHydrator,
        UpdateOrderRequestHydratorInterface $orderRequestHydrator,
        OrderFetcherInterface $orderFetcher
    ) {
        $this->requestHydrator        = $requestHydrator;
        $this->client                 = $client;
        $this->transactionRepository  = $transactionRepository;
        $this->orderRepository        = $orderRepository;
        $this->translator             = $translator;
        $this->requestHasher          = $requestHasher;
        $this->addressRequestHydrator = $addressRequestHydrator;
        $this->orderRequestHydrator   = $orderRequestHydrator;
        $this->orderFetcher           = $orderFetcher;
    }

    /**
     * {@inheritdoc}
     */
    public function pay(AsyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): RedirectResponse
    {
        $request  = $this->requestHydrator->hydrate($transaction, $dataBag, $salesChannelContext);
        $response = $this->client->request($request, $salesChannelContext->getContext());

        if ($response->getHttpStatus() !== 200 || $response->getResponse()['fraud_status'] === 'REJECTED') {
            $errorMessage = $this->translator->trans('KlarnaPayment.errorMessages.paymentDeclined');

            throw new AsyncPaymentProcessException($transaction->getOrderTransaction()->getId(), $errorMessage);
        }

        $this->saveTransactionData($transaction->getOrderTransaction(), $response, $salesChannelContext->getContext());

        return new RedirectResponse($response->getResponse()['redirect_url']);
    }

    /**
     * {@inheritdoc}
     */
    public function finalize(AsyncPaymentTransactionStruct $transaction, Request $request, SalesChannelContext $salesChannelContext): void
    {
        $orderEntity = $this->orderFetcher->getOrderFromOrder(Uuid::fromHexToBytes($transaction->getOrder()->getId()), $salesChannelContext->getContext());

        if (!$orderEntity) {
            $errorMessage = $this->translator->trans('KlarnaPayment.errorMessages.genericError');

            throw new AsyncPaymentFinalizeException($transaction->getOrderTransaction()->getId(), $errorMessage);
        }

        $addressRequest = $this->addressRequestHydrator->hydrate($orderEntity, $salesChannelContext->getContext());
        $orderRequest   = $this->orderRequestHydrator->hydrate($orderEntity, $salesChannelContext->getContext());

        $customFields = $orderEntity->getCustomFields() ?? [];

        $customFields['klarna_order_address_hash'] = $this->requestHasher->getHash($addressRequest);
        $customFields['klarna_order_cart_hash']    = $this->requestHasher->getHash($orderRequest);

        $update = [
            'id'           => $orderEntity->getId(),
            'customFields' => $customFields,
        ];

        $salesChannelContext->getContext()->scope(ContextScope::INTERNAL_SCOPE, function (Context $context) use ($update): void {
            $this->orderRepository->upsert([$update], $context);
        });
    }

    private function saveTransactionData(OrderTransactionEntity $transaction, GenericResponse $response, Context $context): void
    {
        $customFields = $transaction->getCustomFields() ?? [];

        $customFields = array_merge($customFields, [
            'klarna_order_id'     => $response->getResponse()['order_id'],
            'klarna_fraud_status' => $response->getResponse()['fraud_status'],
        ]);

        $update = [
            'id'           => $transaction->getId(),
            'customFields' => $customFields,
        ];

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($update): void {
            $this->transactionRepository->update([$update], $context);
        });
    }
}
