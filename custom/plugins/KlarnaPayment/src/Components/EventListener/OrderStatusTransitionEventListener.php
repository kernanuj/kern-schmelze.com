<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\EventListener;

use KlarnaPayment\Components\Helper\StateHelper\Capture\CaptureStateHelperInterface;
use KlarnaPayment\Components\Helper\StateHelper\Refund\RefundStateHelperInterface;
use KlarnaPayment\Components\Validator\OrderTransitionChangeValidator;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
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

    /** @var EntityRepositoryInterface */
    private $orderRepository;

    /** @var EntityRepositoryInterface */
    private $orderDeliveryRepository;

    public function __construct(
        OrderTransitionChangeValidator $orderStatusValidator,
        CaptureStateHelperInterface $captureStateHelper,
        RefundStateHelperInterface $refundStateHelper,
        EntityRepositoryInterface $orderRepository,
        EntityRepositoryInterface $orderDeliveryRepository
    ) {
        $this->orderStatusValidator    = $orderStatusValidator;
        $this->captureStateHelper      = $captureStateHelper;
        $this->refundStateHelper       = $refundStateHelper;
        $this->orderRepository         = $orderRepository;
        $this->orderDeliveryRepository = $orderDeliveryRepository;
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
        $order   = $this->getOrder($transitionEvent, $context);

        if (!$order) {
            return;
        }

        if ($this->orderStatusValidator->isAutomaticCapture($transitionEvent, $order->getSalesChannelId())) {
            $this->captureStateHelper->processOrderCapture($order, $context);
        } elseif ($this->orderStatusValidator->isAutomaticRefund($transitionEvent, $order->getSalesChannelId())) {
            $this->refundStateHelper->processOrderRefund($order, $context);
        }
    }

    protected function getOrder(StateMachineTransitionEvent $transitionEvent, Context $context): ?OrderEntity
    {
        if ($transitionEvent->getEntityName() === OrderDeliveryDefinition::ENTITY_NAME) {
            $criteria = new Criteria([$transitionEvent->getEntityId()]);
            $criteria->addAssociation('order');
            $criteria->addAssociation('order.transactions');
            $criteria->addAssociation('order.currency');

            /** @var null|OrderDeliveryEntity $orderDeliveryEntity */
            $orderDeliveryEntity = $this->orderDeliveryRepository->search($criteria, $context)->first();

            if (null === $orderDeliveryEntity) {
                return null;
            }

            return $orderDeliveryEntity->getOrder();
        }

        $criteria = new Criteria([$transitionEvent->getEntityId()]);
        $criteria->addAssociation('transactions');
        $criteria->addAssociation('currency');

        return $this->orderRepository->search($criteria, $context)->first();
    }
}
