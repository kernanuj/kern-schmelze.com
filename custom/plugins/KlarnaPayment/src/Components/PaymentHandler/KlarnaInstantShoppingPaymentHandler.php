<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\PaymentHandler;

use KlarnaPayment\Components\Client\Client;
use KlarnaPayment\Components\Client\Hydrator\Request\CreateOrder\CreateOrderRequestHydratorInterface;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\SynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Exception\SyncPaymentProcessException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class KlarnaInstantShoppingPaymentHandler extends AbstractKlarnaPaymentHandler implements SynchronousPaymentHandlerInterface
{
    private const ENDPOINT = '/instantshopping/v1/authorizations/{authorizationToken}/orders';

    /** @var CreateOrderRequestHydratorInterface */
    private $createOrderRequestHydrator;

    /** @var Client */
    private $client;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        CreateOrderRequestHydratorInterface $createOrderRequestHydrator,
        EntityRepositoryInterface $transactionRepository,
        Client $client,
        TranslatorInterface $translator,
        RequestStack $requestStack
    ) {
        $this->createOrderRequestHydrator = $createOrderRequestHydrator;
        $this->transactionRepository      = $transactionRepository;
        $this->client                     = $client;
        $this->translator                 = $translator;
        $this->requestStack               = $requestStack;
    }

    public function pay(SyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): void
    {
        $request = $this->createOrderRequestHydrator->hydrate($transaction, $dataBag, $salesChannelContext);
        $request->assign(['endpoint' => self::ENDPOINT]);

        $response = $this->client->request($request, $salesChannelContext->getContext());

        if ($response->getHttpStatus() !== 200 || $response->getResponse()['fraud_status'] === 'REJECTED') {
            $errorMessage = $this->translator->trans('KlarnaPayment.errorMessages.paymentDeclined');

            throw new SyncPaymentProcessException($transaction->getOrderTransaction()->getId(), $errorMessage);
        }

        $this->saveTransactionData($transaction->getOrderTransaction(), $response, $salesChannelContext->getContext());
    }
}
