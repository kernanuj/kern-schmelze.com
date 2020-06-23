<?php

namespace Sendcloud\Shipping\Core\BusinessLogic\Webhook;

use Exception;
use Sendcloud\Shipping\Core\BusinessLogic\DTO\WebhookDTO;
use Sendcloud\Shipping\Core\BusinessLogic\Interfaces\Configuration;
use Sendcloud\Shipping\Core\BusinessLogic\Interfaces\Configuration as BaseConfiguration;
use Sendcloud\Shipping\Core\BusinessLogic\Interfaces\ConnectService;
use Sendcloud\Shipping\Core\BusinessLogic\Sync\ParcelUpdateTask;
use Sendcloud\Shipping\Core\Infrastructure\Interfaces\Required\TaskQueueStorage;
use Sendcloud\Shipping\Core\Infrastructure\Logger\Logger;
use Sendcloud\Shipping\Core\Infrastructure\ServiceRegister;
use Sendcloud\Shipping\Core\Infrastructure\TaskExecution\Queue;

/**
 * Class WebhookEventHandler
 * @package Sendcloud\Shipping\Core\BusinessLogic\Webhook
 */
class WebhookEventHandler
{
    /**
     * @var WebhookHelper
     */
    private $webhookHelper;
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * Routes for webhook events.
     *
     * @param WebhookDTO $webhookDTO SendCloud webhook DTO.
     *
     * @return bool
     * @throws Exception
     * @see https://docs.sendcloud.sc/api/v2/shipping/#webhooks
     */
    public function handle(WebhookDTO $webhookDTO)
    {
        $this->getConfiguration()->setContext($webhookDTO->getContext());

        Logger::logDebug($webhookDTO->getRawBody(), 'Integration');

        $body = $webhookDTO->getBody();
        $action = isset($body['action']) ? $body['action'] : '';

        switch ($action) {
            case WebhookHelper::PARCEL_STATUS_CHANGED:
                return $this->handleParcelStatusAction($webhookDTO);
            case WebhookHelper::INTEGRATION_UPDATED:
            case WebhookHelper::INTEGRATION_CONNECTED:
                return $this->handleIntegrationAction($webhookDTO);
            case WebhookHelper::INTEGRATION_DELETED:
                return $this->handleIntegrationDelete($webhookDTO);
            case WebhookHelper::INTEGRATION_CREDENTIALS:
                return $this->handleIntegrationCredentials($webhookDTO);
            default:
                return false;
        }
    }

    /**
     * Queues order status update task.
     *
     * @param WebhookDTO $webhookDTO
     *
     * @see https://docs.sendcloud.sc/api/v2/shipping/#parcel-status-change
     * @return bool
     */
    protected function handleParcelStatusAction(WebhookDTO $webhookDTO)
    {
        try {
            if (!$this->getWebhookHelper()->isValid($webhookDTO->getHash(), $webhookDTO->getRawBody())) {
                return false;
            }

            $webhookParcel = $this->getWebhookHelper()->parseParcelPayload($webhookDTO->getBody());

            /** @var Queue $queue */
            $queue = ServiceRegister::getService(Queue::CLASS_NAME);
            $queue->enqueue(
                $this->getConfiguration()->getQueueName(),
                new ParcelUpdateTask(
                    $webhookParcel->getShipmentUuid(),
                    $webhookParcel->getOrderId(),
                    $webhookParcel->getOrderNumber(),
                    $webhookParcel->getParcelId(),
                    $webhookParcel->getTimestamp()
                ),
                $webhookDTO->getContext()
            );

        } catch ( Exception $e) {
            Logger::logError($e->getMessage(), 'Integration');
            return false;
        }

        return true;
    }

    /**
     * Updates integration configuration.
     *
     * @param WebhookDTO $webhookDTO
     *
     * @return bool
     */
    protected function handleIntegrationAction(WebhookDTO $webhookDTO)
    {
        try {
            if (!$this->getWebhookHelper()->isValid($webhookDTO->getHash(), $webhookDTO->getRawBody())) {
                return false;
            }

            $webhookIntegration = $this->getWebhookHelper()->parseIntegrationPayload($webhookDTO->getBody());
            $this->getConfiguration()->setServicePointEnabled($webhookIntegration->isServicePointsEnabled());
            $this->getConfiguration()->setCarriers($webhookIntegration->getCarriers());
        } catch ( Exception $e) {
            Logger::logError($e->getMessage(), 'Integration');
            return false;
        }

        return true;
    }

    /**
     * When integration is deleted on SendCloud side, reset credentials, disable service point delivery
     * and remove all carriers.
     *
     * @param WebhookDTO $webhookDTO
     * @return bool
     */
    protected function handleIntegrationDelete(WebhookDTO $webhookDTO)
    {
        // TODO: Webhook request should be checked here instead of webhook token, but Sendcloud does not set
        // TODO: `Sendcloud-Signature` header for delete webhook request. Once Sendcloud fix this bug we should switch
        // TODO: to request validation based on `Sendcloud-Signature` header (uncomment following block and remove token validation)
//        if (!$this->getWebhookHelper()->isValid($webhookDTO->getHash(), $webhookDTO->getRawBody())) {
//            return false;
//        }

        if ($this->getConfiguration()->getWebhookToken() !== $webhookDTO->getToken()) {
            return false;
        }

        $this->getConfiguration()->resetAuthorizationCredentials();
        $this->getConfiguration()->setServicePointEnabled(false);
        $this->getConfiguration()->setCarriers();

        /** @var TaskQueueStorage $queueStorage */
        $queueStorage = ServiceRegister::getService(TaskQueueStorage::CLASS_NAME);
        return $queueStorage->deleteByType('InitialSyncTask', $webhookDTO->getContext());

    }

    /**
     * Queues integration connect task.
     *
     * @param WebhookDTO $webhookDTO
     *
     * @see https://docs.sendcloud.sc/api/v2/shipping/#integration-connected
     * @return bool
     */
    protected function handleIntegrationCredentials(WebhookDTO $webhookDTO)
    {
        try {
            /** @var ConnectService $connectService */
            $connectService = ServiceRegister::getService(ConnectService::CLASS_NAME);
            $credentials = $this->getWebhookHelper()->parseIntegrationCredentials($webhookDTO->getBody());
            $success = $connectService->isCallbackValid($credentials, $webhookDTO->getToken());

            if ($success) {
                $connectService->initializeConnection($credentials);
            }

        } catch ( Exception $e) {
            Logger::logError($e->getMessage(), 'Integration');
            $this->getConfiguration()->resetAuthorizationCredentials();
            $success = false;
        }

        return $success;
    }

    /**
     * Returns an instance of configuration service.
     *
     * @return Configuration
     */
    protected function getConfiguration()
    {
        if (!$this->configuration) {
            $this->configuration = ServiceRegister::getService(BaseConfiguration::CLASS_NAME);
        }

        return $this->configuration;
    }

    /**
     * Returns an instance of webhook helper class.
     *
     * @return WebhookHelper
     */
    protected function getWebhookHelper()
    {
        if (!$this->webhookHelper) {
            $this->webhookHelper = new WebhookHelper();
        }

        return $this->webhookHelper;
    }

}
