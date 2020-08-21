<?php

namespace Sendcloud\Shipping\Controller\API\Backend;

use Sendcloud\Shipping\Core\BusinessLogic\Interfaces\ConnectService;
use Sendcloud\Shipping\Service\Utility\Initializer;
use Shopware\Core\Framework\Api\Response\JsonApiResponse;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CheckStatusController
 *
 * @package Sendcloud\Shipping\Controller\API\Backend
 */
class CheckStatusController extends AbstractController
{
    /**
     * @var ConnectService
     */
    private $connectService;

    /**
     * CheckStatusController constructor.
     *
     * @param Initializer $initializer
     * @param ConnectService $connectService
     */
    public function __construct(Initializer $initializer, ConnectService $connectService)
    {
        $initializer->registerServices();
        $this->connectService = $connectService;
    }

    /**
     * Returns connection status
     *
     * @RouteScope(scopes={"api"})
     * @Route(path="/api/v{version}/sendcloud/connectionStatus", name="api.sendcloud.connectionStatus", methods={"GET", "POST"})
     *
     * @return JsonApiResponse
     **/
    public function checkConnectionStatus(): JsonApiResponse
    {
        $data = ['isConnected' => $this->connectService->isIntegrationConnected()];

        return new JsonApiResponse($data);
    }
}
