<?php

namespace Sendcloud\Shipping\Controller\API\Backend;

use Sendcloud\Shipping\Core\Infrastructure\Interfaces\Exposed\TaskRunnerWakeup;
use Sendcloud\Shipping\Core\Infrastructure\Interfaces\Required\Configuration;
use Sendcloud\Shipping\Entity\Config\SystemConfigurationRepository;
use Sendcloud\Shipping\Service\Utility\Initializer;
use Shopware\Core\Framework\Api\Response\JsonApiResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DashboardController
 *
 * @package Sendcloud\Shipping\Controller\API\Backend
 */
class DashboardController extends AbstractController
{
    public const SENDCLOUD_URL = 'https://panel.sendcloud.sc/';

    /**
     * @var \Sendcloud\Shipping\Core\BusinessLogic\Interfaces\Configuration
     */
    private $configService;
    /**
     * @var TaskRunnerWakeup
     */
    private $wakeupService;
    /**
     * @var SystemConfigurationRepository
     */
    private $systemConfigurationRepository;

    /**
     * DashboardController constructor.
     *
     * @param Initializer $initializer
     * @param Configuration $configService
     * @param TaskRunnerWakeup $wakeupService
     */
    public function __construct(
        Initializer $initializer,
        Configuration $configService,
        TaskRunnerWakeup $wakeupService,
        SystemConfigurationRepository $systemConfigurationRepository
    ) {
        $initializer->registerServices();
        $this->configService = $configService;
        $this->wakeupService = $wakeupService;
        $this->systemConfigurationRepository = $systemConfigurationRepository;
    }

    /**
     * Returns dashboard configuration
     *
     * @RouteScope(scopes={"api"})
     * @Route(path="/api/v{version}/sendcloud/dashboard", name="api.sendcloud.dashboard", methods={"GET", "POST"})
     *
     * @return JsonApiResponse
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function getDashboardConfig(): JsonApiResponse
    {
        $this->wakeupService->wakeup();
        $data = [
            'isServicePointEnabled' => $this->configService->isServicePointEnabled(),
            'salesChannel' => $this->systemConfigurationRepository->getDefaultShopName(),
            'sendcloudUrl' => static::SENDCLOUD_URL,
        ];

        return new JsonApiResponse($data);
    }
}
