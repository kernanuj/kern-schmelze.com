<?php

namespace Sendcloud\Shipping\Controller\API\Backend;

use Doctrine\DBAL\DBALException;
use Sendcloud\Shipping\Core\Infrastructure\Interfaces\Required\Configuration;
use Sendcloud\Shipping\Core\Infrastructure\Interfaces\Required\TaskQueueStorage;
use Sendcloud\Shipping\Core\Infrastructure\Logger\Logger;
use Sendcloud\Shipping\Entity\ShippingMethod\ShippingMethodRepository;
use Sendcloud\Shipping\Service\Business\ConfigurationService;
use Sendcloud\Shipping\Service\Utility\Initializer;
use Shopware\Core\Framework\Api\Response\JsonApiResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class SupportController
 *
 * @package Sendcloud\Shipping\Controller\API\Backend
 */
class SupportController extends AbstractController
{
    /**
     * @var ConfigurationService
     */
    private $configService;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var ShippingMethodRepository
     */
    private $shippingMethodRepository;
    /**
     * @var TaskQueueStorage
     */
    private $taskQueueStorage;

    /**
     * SupportController constructor.
     *
     * @param Initializer $initializer
     * @param Configuration $configService
     * @param UrlGeneratorInterface $urlGenerator
     * @param ShippingMethodRepository $shippingMethodRepository
     * @param TaskQueueStorage $taskQueueStorage
     */
    public function __construct(
        Initializer $initializer,
        Configuration $configService,
        UrlGeneratorInterface $urlGenerator,
        ShippingMethodRepository $shippingMethodRepository,
        TaskQueueStorage $taskQueueStorage
    ) {
        $initializer->registerServices();
        $this->configService = $configService;
        $this->urlGenerator = $urlGenerator;
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->taskQueueStorage = $taskQueueStorage;
    }

    /**
     * Returns all configuration parameters for diagnostics purposes.
     *
     * @RouteScope(scopes={"api"})
     * @Route(path="/api/v1/sendcloud/support", name="api.sendcloud.support", methods={"GET"})
     */
    public function getConfigParameters(): JsonApiResponse
    {

        try {
            $data = [
                'SENDCLOUD_INTEGRATION_ID' => $this->configService->getIntegrationId(),
                'SENDCLOUD_INTEGRATION_NAME' => $this->configService->getIntegrationName(),
                'SENDCLOUD_DEFAULT_SHOP_NAME' => $this->configService->getShopName(),
                'SENDCLOUD_MIN_LOG_LEVEL' => $this->configService->getMinLogLevel(),
                'SENDCLOUD_DEFAULT_LOGGER_STATUS' => $this->configService->isDefaultLoggerEnabled(),
                'SENDCLOUD_MAX_STARTED_TASK_LIMIT' => $this->configService->getMaxStartedTasksLimit(),
                'SENDCLOUD_MAX_TASK_EXECUTION_RETRIES' => $this->configService->getMaxTaskExecutionRetries(),
                'SENDCLOUD_MAX_TASK_INACTIVITY_PERIOD' => $this->configService->getMaxTaskInactivityPeriod(),
                'SENDCLOUD_MAX_ALIVE_TIME' => $this->configService->getTaskRunnerMaxAliveTime(),
                'SENDCLOUD_TASK_RUNNER_STATUS' => $this->configService->getTaskRunnerStatus(),
                'SENDCLOUD_TASK_RUNNER_WAKEUP_DELAY' => $this->configService->getTaskRunnerWakeupDelay(),
                'SENDCLOUD_QUEUE_NAME' => $this->configService->getQueueName(),
                'SENDCLOUD_SHIPPING_METHOD_ENABLED' => $this->isSendCloudShippingMethodEnabled(),
                'SENDCLOUD_WEBHOOK_URL' => $this->configService->getWebHookEndpoint(),
                'SENDCLOUD_SERVICE_POINT_ENABLED' => $this->configService->isServicePointEnabled(),
                'SENDCLOUD_CARRIERS' => $this->configService->getCarriers(),
                'SENDCLOUD_COMPLETED_TASKS_RETENTION_PERIOD' => $this->configService->getCompletedTasksRetentionPeriod(),
                'SENDCLOUD_FAILED_TASKS_RETENTION_PERIOD' => $this->configService->getFailedTasksRetentionPeriod(),
                'SENDCLOUD_OLD_TASKS_CLEANUP_THRESHOLD' => $this->configService->getOldTaskCleanupTimeThreshold(),
                'SENDCLOUD_ASYNC_PROCESS_STARTER_URL' => $this->urlGenerator->generate('api.sendcloud.async', ['guid' => 'guid'], UrlGeneratorInterface::ABSOLUTE_URL),
                'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'],
                'PHP_VERSION' => PHP_VERSION,
                'PHP_TIME_LIMIT' => ini_get('max_execution_time'),
                'PHP_CURL_LIBRARY' => function_exists('curl_version'),
                'QUEUE_ITEMS' => $this->getQueueItems(),
            ];
        } catch (\Exception $exception) {
            Logger::logError("An error occurred when fetching configuration: {$exception->getMessage()}", 'Integration');
            $data = ['error' => $exception->getMessage()];
        }


        return new JsonApiResponse($data);
    }

    /**
     * Updates configuration from POST request.
     *
     * @RouteScope(scopes={"api"})
     * @Route(path="/api/v1/sendcloud/support/update", name="api.sendcloud.support.update", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonApiResponse
     */
    public function update(Request $request): JsonApiResponse
    {
        try {
            $payload = json_decode($request->getContent(), true);

            if (array_key_exists('SENDCLOUD_MIN_LOG_LEVEL', $payload)) {
                $this->configService->saveMinLogLevel((int)$payload['SENDCLOUD_MIN_LOG_LEVEL']);
            }

            if (array_key_exists('SENDCLOUD_DEFAULT_LOGGER_STATUS', $payload)) {
                $this->configService->setDefaultLoggerEnabled((bool)$payload['SENDCLOUD_DEFAULT_LOGGER_STATUS']);
            }

            if (array_key_exists('SENDCLOUD_MAX_STARTED_TASK_LIMIT', $payload)) {
                $this->configService->setMaxStartedTaskLimit((int)$payload['SENDCLOUD_MAX_STARTED_TASK_LIMIT']);
            }

            if (array_key_exists('SENDCLOUD_TASK_RUNNER_WAKEUP_DELAY', $payload)) {
                $this->configService->setTaskRunnerWakeUpDelay((int)$payload['SENDCLOUD_TASK_RUNNER_WAKEUP_DELAY']);
            }

            if (array_key_exists('SENDCLOUD_MAX_ALIVE_TIME', $payload)) {
                $this->configService->setTaskRunnerMaxAliveTime((int)$payload['SENDCLOUD_MAX_ALIVE_TIME']);
            }

            if (array_key_exists('SENDCLOUD_MAX_TASK_EXECUTION_RETRIES', $payload)) {
                $this->configService->setMaxTaskExecutionRetries((int)$payload['SENDCLOUD_MAX_TASK_EXECUTION_RETRIES']);
            }

            if (array_key_exists('SENDCLOUD_MAX_TASK_INACTIVITY_PERIOD', $payload)) {
                $this->configService->setMaxTaskInactivityPeriod((int)$payload['SENDCLOUD_MAX_TASK_INACTIVITY_PERIOD']);
            }

            if (array_key_exists('SENDCLOUD_RESET_AUTHENTICATION_CREDENTIALS', $payload)) {
                $this->configService->resetAuthorizationCredentials();
            }

            if (array_key_exists('SENDCLOUD_COMPLETED_TASKS_RETENTION_PERIOD', $payload)) {
                $this->configService->setCompletedTasksRetentionPeriod(
                    (int)$payload['SENDCLOUD_COMPLETED_TASKS_RETENTION_PERIOD']
                );
            }

            if (array_key_exists('SENDCLOUD_FAILED_TASKS_RETENTION_PERIOD', $payload)) {
                $this->configService->setFailedTasksRetentionPeriod(
                    (int)$payload['SENDCLOUD_FAILED_TASKS_RETENTION_PERIOD']
                );
            }

            if (array_key_exists('SENDCLOUD_OLD_TASKS_CLEANUP_THRESHOLD', $payload)) {
                $this->configService->setOldTaskCleanupTimeThreshold(
                    (int)$payload['SENDCLOUD_OLD_TASKS_CLEANUP_THRESHOLD']
                );
            }

            return new JsonApiResponse(['message' => 'Successfully updated config values!']);
        } catch (\Exception $exception) {
            return new JsonApiResponse(['error' => $exception->getMessage()]);
        }
    }

    /**
     * Check if service point shipping option is enabled in system
     *
     * @return bool
     * @throws DBALException
     * @throws InconsistentCriteriaIdsException
     */
    private function isSendCloudShippingMethodEnabled(): bool
    {
        $servicePointId = $this->configService->getSendCloudServicePointDeliveryMethodId();
        if ($servicePointId) {
            $shippingMethod = $this->shippingMethodRepository->getShippingMethodById($servicePointId);
            if ($shippingMethod) {
                return $shippingMethod->getActive();
            }
        }

        return false;
    }

    /**
     * Get last 10 queue items
     *
     * @return array
     */
    private function getQueueItems(): array
    {
        $queueItemsMap = [];
        $queueItems = $this->taskQueueStorage->findAll();
        foreach ($queueItems as $queueItem) {
            $queueItemsMap[] = $this->taskQueueStorage->toArray($queueItem);
        }

        return $queueItemsMap;
    }
}
