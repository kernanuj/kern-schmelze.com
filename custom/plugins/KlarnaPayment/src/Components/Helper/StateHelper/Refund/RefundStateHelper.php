<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\StateHelper\Refund;

use KlarnaPayment\Components\Client\Client;
use KlarnaPayment\Components\Client\Hydrator\Request\CreateRefund\CreateRefundRequestHydratorInterface;
use KlarnaPayment\Components\Helper\StateHelper\StateData\StateDataHelperInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\StateMachine\Exception\IllegalTransitionException;

class RefundStateHelper implements RefundStateHelperInterface
{
    /** @var CreateRefundRequestHydratorInterface */
    private $refundRequestHydrator;

    /** @var OrderTransactionStateHandler */
    private $transactionStateHandler;

    /** @var StateDataHelperInterface */
    private $stateDataHelper;

    /** @var LoggerInterface */
    private $logger;

    /** @var Client */
    private $client;

    public function __construct(
        CreateRefundRequestHydratorInterface $refundRequestHydrator,
        OrderTransactionStateHandler $transactionStateHandler,
        StateDataHelperInterface $stateDataHelper,
        LoggerInterface $logger,
        Client $client
    ) {
        $this->refundRequestHydrator   = $refundRequestHydrator;
        $this->transactionStateHandler = $transactionStateHandler;
        $this->stateDataHelper         = $stateDataHelper;
        $this->client                  = $client;
        $this->logger                  = $logger;
    }

    public function processOrderRefund(OrderEntity $order, Context $context): void
    {
        if (null === $order->getTransactions()) {
            return;
        }

        foreach ($order->getTransactions()->getElements() as $transaction) {
            $this->refundTransaction($transaction, $order, $context);
        }
    }

    private function refundTransaction(OrderTransactionEntity $transaction, OrderEntity $order, Context $context): void
    {
        if (null === $order->getCurrency()) {
            return;
        }

        $customFields = $transaction->getCustomFields();

        if (empty($customFields['klarna_order_id'])) {
            return;
        }

        $klarnaOrder = $this->stateDataHelper->getKlarnaOrder(
            $customFields['klarna_order_id'],
            $order->getSalesChannelId(),
            $context
        );

        if (empty($klarnaOrder)) {
            return;
        }

        if ($klarnaOrder['captured_amount'] <= 0) {
            return;
        }

        $refundAmount = (int) ($klarnaOrder['captured_amount'] - $klarnaOrder['refunded_amount']);

        if ($refundAmount <= 0) {
            return;
        }

        $dataBag = $this->stateDataHelper->prepareDataBag(
            $order,
            $klarnaOrder,
            $order->getSalesChannelId()
        );

        $dataBag->set('refundAmount', $refundAmount / (10 ** $order->getCurrency()->getDecimalPrecision()));

        $request  = $this->refundRequestHydrator->hydrate($dataBag, $context);
        $response = $this->client->request($request, $context);

        if ($response->getHttpStatus() !== 201) {
            return;
        }

        try {
            $this->transactionStateHandler->refund($transaction->getId(), $context);
        } catch (IllegalTransitionException $exception) {
            $this->logger->notice($exception->getMessage(), $exception->getParameters());
        }
    }
}
