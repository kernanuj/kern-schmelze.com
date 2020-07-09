<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Controller\Administration;

use KlarnaPayment\Components\ButtonKeyHandler\ButtonKeyHandlerInterface;
use KlarnaPayment\Components\Client\Client;
use KlarnaPayment\Components\Client\Hydrator\Request\Test\TestRequestHydratorInterface;
use KlarnaPayment\Components\Exception\ButtonKeyCreationFailed;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class SettingsController extends AbstractController
{
    /** @var Client */
    private $client;

    /** @var LoggerInterface */
    private $logger;

    /** @var TestRequestHydratorInterface */
    private $requestHydrator;

    /** @var ButtonKeyHandlerInterface */
    private $buttonKeyHandler;

    public function __construct(
        Client $client,
        LoggerInterface $logger,
        TestRequestHydratorInterface $requestHydrator,
        ButtonKeyHandlerInterface $buttonKeyHandler
    ) {
        $this->client           = $client;
        $this->logger           = $logger;
        $this->requestHydrator  = $requestHydrator;
        $this->buttonKeyHandler = $buttonKeyHandler;
    }

    /**
     * @Route("/api/v{version}/_action/klarna_payment/validate-credentials", name="api.action.klarna_payment.validate.credentials", methods={"POST"})
     */
    public function validateCredentials(RequestDataBag $dataBag, Context $context): JsonResponse
    {
        $liveError = false;
        $testError = false;

        $liveCredentialsValid = $this->validate(
            $dataBag->get('apiUsername', ''),
            $dataBag->get('apiPassword', ''),
            false,
            $dataBag->get('salesChannel'),
            $context
        );

        if (!$liveCredentialsValid) {
            $liveError = true;
        }

        if ($dataBag->get('testMode', false)) {
            $testCredentialsValid = $this->validate(
                $dataBag->get('testApiUsername', ''),
                $dataBag->get('testApiPassword', ''),
                true,
                $dataBag->get('salesChannel'),
                $context
            );

            if (!$testCredentialsValid) {
                $testError = true;
            }
        }

        if ($liveError || $testError) {
            return new JsonResponse(['status' => 'error', 'live' => $liveError, 'test' => $testError], 400);
        }

        return new JsonResponse(['status' => 'success'], 200);
    }

    /**
     * @Route("/api/v{version}/_action/klarna_payment/create-button-keys", name="api.action.klarna_payment.button_keys.create", methods={"POST"})
     */
    public function createButtonKeys(RequestDataBag $dataBag, Context $context): JsonResponse
    {
        try {
            if ($dataBag->get('salesChannel') === null) {
                $this->buttonKeyHandler->createButtonKeysForAllDomains($context);
            } else {
                $this->buttonKeyHandler->createButtonKeysBySalesChannelId($dataBag->get('salesChannel'), $context);
            }
        } catch (ButtonKeyCreationFailed $e) {
            return new JsonResponse(
                [
                    'status'  => 'error',
                    'message' => 'klarna-payment-configuration.settingsForm.messages.messageButtonKeyCreateError',
                    'data'    => ['code' => $e->getErrorCode(), 'message' => $e->getMessage()],
                ], Response::HTTP_BAD_REQUEST
            );
        }

        return new JsonResponse(['status' => 'success'], 200);
    }

    private function validate(
        string $username,
        string $password,
        bool $testMode,
        ?string $salesChannel,
        Context $context): bool
    {
        $request  = $this->requestHydrator->hydrate($username, $password, $testMode, $salesChannel);
        $response = $this->client->request($request, $context);

        $status = $response->getHttpStatus() === 404 && $response->getResponse()['error_code'] === 'NO_SUCH_ORDER';

        $this->logger->info('klarna plugin credentials validated', [
            'success'      => $status,
            'salesChannel' => $salesChannel ?? 'all',
            'response'     => $response,
        ]);

        return $status;
    }
}
