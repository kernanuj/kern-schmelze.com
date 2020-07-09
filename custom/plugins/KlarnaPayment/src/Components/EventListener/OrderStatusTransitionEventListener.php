<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\EventListener;

use KlarnaPayment\Components\Helper\OrderFetcher;
use KlarnaPayment\Components\Helper\StateHelper\Cancel\CancelStateHelper;
use KlarnaPayment\Components\Helper\StateHelper\Capture\CaptureStateHelperInterface;
use KlarnaPayment\Components\Helper\StateHelper\Refund\RefundStateHelperInterface;
use KlarnaPayment\Components\Validator\OrderTransitionChangeValidator;
use KlarnaPayment\Core\Framework\ContextScope;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderStatusTransitionEventListener implements EventSubscriberInterface
{
    /** @var OrderTransitionChangeValidator */
    private $orderStatusValidator;

    /** @var CaptureStateHelperInterface */
    private $captureStateHelper;

    /** @var RefundStateHelperInterface */
    private $refundStateHelper;

    /** @var OrderFetcher */
    private $orderFetcher;

    /** @var CancelStateHelper */
    private $cancelStateHelper;

    public function __construct(
        OrderTransitionChangeValidator $orderStatusValidator,
        CaptureStateHelperInterface $captureStateHelper,
        RefundStateHelperInterface $refundStateHelper,
        CancelStateHelper $cancelStateHelper,
        OrderFetcher $orderFetcher
    ) {
        $this->orderStatusValidator = $orderStatusValidator;
        $this->captureStateHelper   = $captureStateHelper;
        $this->refundStateHelper    = $refundStateHelper;
        $this->cancelStateHelper    = $cancelStateHelper;
        $this->orderFetcher         = $orderFetcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StateMachineTransitionEvent::class => 'onStateMachineTransition',
        ];
    }

    public function onStateMachineTransition(StateMachineTransitionEvent $transitionEvent): void
    {
        $context = $transitionEvent->getContext();

        if ($transitionEvent->getContext()->getScope() === ContextScope::INTERNAL_SCOPE) {
            return;
        }

        $order = $this->getOrder($transitionEvent, $context);

        if (!$order) {
            return;
        }

        if ($this->orderStatusValidator->isAutomaticCapture($transitionEvent, $order->getSalesChannelId())) {
            $this->captureStateHelper->processOrderCapture($order, $context);
        } elseif ($this->orderStatusValidator->isAutomaticRefund($transitionEvent, $order->getSalesChannelId())) {
            $this->refundStateHelper->processOrderRefund($order, $context);
        }

        if ($this->orderStatusValidator->isAutomaticCancel($transitionEvent)) {
            if ($transitionEvent->getEntityName() === OrderDefinition::ENTITY_NAME) {
                $this->cancelStateHelper->processOrderCancellation($order, $context);
            } elseif ($transitionEvent->getEntityName() === OrderTransactionDefinition::ENTITY_NAME) {
                $this->cancelTransaction($order, $transitionEvent->getEntityId(), $context);
            }
        }
    }

    private function getOrder(StateMachineTransitionEvent $transitionEvent, Context $context): ?OrderEntity
    {
        if ($transitionEvent->getEntityName() === OrderDefinition::ENTITY_NAME) {
            return $this->orderFetcher->getOrderFromOrder($transitionEvent->getEntityId(), $context);
        }

        if ($transitionEvent->getEntityName() === OrderDeliveryDefinition::ENTITY_NAME) {
            return $this->orderFetcher->getOrderFromOrderDelivery($transitionEvent->getEntityId(), $context);
        }

        if ($transitionEvent->getEntityName() === OrderTransactionDefinition::ENTITY_NAME) {
            return $this->orderFetcher->getOrderFromOrderTransaction($transitionEvent->getEntityId(), $context);
        }

        return null;
    }

    private function cancelTransaction(OrderEntity $order, string $transactionId, Context $context): void
    {
        if (null === $order->getTransactions()) {
            return;
        }

        $transaction = $order->getTransactions()->get($transactionId);

        if (null === $transaction) {
            return;
        }

        $this->cancelStateHelper->processOrderTransactionCancellation($transaction, $order, $context);
    }
}
