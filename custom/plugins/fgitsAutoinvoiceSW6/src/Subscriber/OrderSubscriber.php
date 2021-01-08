<?php declare(strict_types=1);

namespace Fgits\AutoInvoice\Subscriber;

use Fgits\AutoInvoice\ScheduledTask\Export\AutoInvoiceExportTask;
use Fgits\AutoInvoice\ScheduledTask\OrderScan\AutoInvoiceOrderScanTask;
use Fgits\AutoInvoice\Service\FgitsLibrary\ScheduledTask;
use Fgits\AutoInvoice\Service\OrderProcessor;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\Exception\OrderTransactionNotFoundException;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\MailTemplate\Exception\SalesChannelNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\StateMachine\Event\StateMachineStateChangeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 */
class OrderSubscriber implements EventSubscriberInterface
{
    /**
     * @var ScheduledTask $scheduledTask
     */
    private $scheduledTask;

    /**
     * @var OrderProcessor $orderProcessor
     */
    private $orderProcessor;

    /**
     * @var EntityRepositoryInterface $orderRepository
     */
    private $orderRepository;

    /**
     * @var EntityRepositoryInterface $orderTransactionRepository
     */
    private $orderTransactionRepository;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * OrderSubscriber constructor.
     *
     * @param ScheduledTask $scheduledTask
     * @param OrderProcessor $orderProcessor
     * @param EntityRepositoryInterface $orderRepository
     * @param EntityRepositoryInterface $orderTransactionRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScheduledTask $scheduledTask,
        OrderProcessor $orderProcessor,
        EntityRepositoryInterface $orderRepository,
        EntityRepositoryInterface $orderTransactionRepository,
        LoggerInterface $logger
    ) {
        $this->scheduledTask              = $scheduledTask;
        $this->orderProcessor             = $orderProcessor;
        $this->orderRepository            = $orderRepository;
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->logger                     = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return[
            CheckoutOrderPlacedEvent::class => [['scheduleScheduledTasks'], ['onOrderPlaced']],
            'state_machine.order_transaction.state_changed' => 'onOrderTransactionStateChanged',
            'state_machine.order.state_changed' => 'onOrderStateChanged'
        ];
    }

    public function scheduleScheduledTasks()
    {
        $this->scheduledTask->schedule(AutoInvoiceExportTask::getTaskName());
        $this->scheduledTask->schedule(AutoInvoiceOrderScanTask::getTaskName());
    }

    /**
     * Gets invoked when an order is created.
     *
     * @param CheckoutOrderPlacedEvent $event
     *
     * @throws InconsistentCriteriaIdsException
     * @throws SalesChannelNotFoundException
     */
    public function onOrderPlaced(CheckoutOrderPlacedEvent $event)
    {
        $order = $event->getOrder();

        if ($order instanceof OrderEntity) {
            $this->orderProcessor->processOrder($order, true);
        } else {
            $this->logger->error(sprintf('Event %s did not receive a proper ordernumber. Unable to get Order-object. Aborting.', $event->getName()));
        }
    }

    /**
     * Gets invoked when the payment status is changed.
     *
     * @param StateMachineStateChangeEvent $event
     *
     * @throws InconsistentCriteriaIdsException
     * @throws SalesChannelNotFoundException
     * @throws OrderTransactionNotFoundException
     */
    public function onOrderTransactionStateChanged(StateMachineStateChangeEvent $event)
    {
        $orderTransactionId = $event->getTransition()->getEntityId();

        /** @var OrderTransactionEntity|null $orderTransaction */
        $orderTransaction = $this->orderTransactionRepository->search(
            new Criteria([$orderTransactionId]),
            $event->getContext()
        )->first();

        if ($orderTransaction === null) {
            throw new OrderTransactionNotFoundException($orderTransactionId);
        }

        $orderId = $orderTransaction->getOrderId();

        /** @var OrderEntity|null $order */
        $order = $this->orderRepository->search(new Criteria([$orderId]), $event->getContext())->get($orderId);

        if ($order instanceof OrderEntity) {
            $this->orderProcessor->processOrder($order);
        } else {
            $this->logger->error(sprintf('Event %s did not receive a proper ordernumber. Unable to get Order-object. Aborting.', $event->getName()));
        }
    }

    /**
     * Gets invoked when the order status is changed.
     *
     * @param StateMachineStateChangeEvent $event
     * 
     * @throws InconsistentCriteriaIdsException
     * @throws SalesChannelNotFoundException
     */
    public function onOrderStateChanged(StateMachineStateChangeEvent $event)
    {
        $orderId = $event->getTransition()->getEntityId();

        /** @var OrderEntity|null $order */
        $order = $this->orderRepository->search(new Criteria([$orderId]), $event->getContext())->get($orderId);

        if ($order instanceof OrderEntity) {
            $this->orderProcessor->processOrder($order);
        } else {
            $this->logger->error(sprintf('Event %s did not receive a proper ordernumber. Unable to get Order-object. Aborting.', $event->getName()));
        }
    }
}
