<?php

namespace Sendcloud\Shipping\Handlers;

use Sendcloud\Shipping\Core\BusinessLogic\DTO\WebhookParcelDTO;
use Sendcloud\Shipping\Core\BusinessLogic\Sync\ParcelUpdateTask;
use Sendcloud\Shipping\Core\BusinessLogic\Webhook\WebhookEventHandler as BaseHandler;
use Sendcloud\Shipping\Core\Infrastructure\ServiceRegister;
use Sendcloud\Shipping\Core\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use Sendcloud\Shipping\Core\Infrastructure\TaskExecution\Queue;
use Sendcloud\Shipping\Interfaces\EntityQueueNameAware;

/**
 * Class WebhookHandler
 *
 * @package Sendcloud\Shipping\Handlers
 */
class WebhookHandler extends BaseHandler
{
    /**
     * Enqueues task for parcel update.
     *
     * @param WebhookParcelDTO $webhookParcel
     * @param string $context
     * @throws QueueStorageUnavailableException
     *
     * @noinspection PhpMissingParamTypeInspection
     */
    protected function enqueueParcelUpdateTask(WebhookParcelDTO $webhookParcel, $context)
    {
        /** @var EntityQueueNameAware $config */
        $config = $this->getConfiguration();
        /** @var Queue $queue */
        $queue = ServiceRegister::getService(Queue::CLASS_NAME);
        $queue->enqueue(
            $config->getEntityQueueName('order', $webhookParcel->getOrderId()),
            new ParcelUpdateTask(
                $webhookParcel->getShipmentUuid(),
                $webhookParcel->getOrderId(),
                $webhookParcel->getOrderNumber(),
                $webhookParcel->getParcelId(),
                $webhookParcel->getTimestamp()
            ),
            $context
        );
    }
}