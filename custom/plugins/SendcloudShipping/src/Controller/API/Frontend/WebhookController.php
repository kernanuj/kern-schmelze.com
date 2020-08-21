<?php

namespace Sendcloud\Shipping\Controller\API\Frontend;

use Sendcloud\Shipping\Core\BusinessLogic\DTO\WebhookDTO;
use Sendcloud\Shipping\Core\BusinessLogic\Webhook\WebhookEventHandler;
use Sendcloud\Shipping\Service\Utility\Initializer;
use Shopware\Core\Framework\Api\Response\JsonApiResponse;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class WebhookController
 *
 * @package Sendcloud\Shipping\Controller\API\Frontend
 */
class WebhookController extends AbstractController
{
    /**
     * @var WebhookEventHandler
     */
    private $webhookEventHandler;

    /**
     * WebhookController constructor.
     *
     * @param Initializer $initializer
     * @param WebhookEventHandler $webhookEventHandler
     */
    public function __construct(Initializer $initializer, WebhookEventHandler $webhookEventHandler)
    {
        $initializer->registerServices();
        $this->webhookEventHandler = $webhookEventHandler;
    }

    /**
     * Handles webhook request from SendCloud
     *
     * @RouteScope(scopes={"api"})
     * @Route(path="/api/sendcloud/webhook/{token}", name="api.sendcloud.webhook", defaults={"auth_required"=false}, methods={"GET", "POST"})
     *
     * @param Request $request
     * @param string $token endpoint token
     *
     * @return JsonApiResponse
     *
     * @throws \Exception
     */
    public function handle(Request $request, string $token = null): JsonApiResponse
    {
        $hash = $request->server->get('HTTP_SENDCLOUD_SIGNATURE', '');
        $token = $token ?: '';

        $webhookDTO = new WebhookDTO($request->getContent(), $hash, $token);
        $success = $this->webhookEventHandler->handle($webhookDTO);

        return new JsonApiResponse(
            ['success' => $success],
            $success ? Response::HTTP_OK : Response::HTTP_CONFLICT
        );
    }
}
