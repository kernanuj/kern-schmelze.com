<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Controller\Administration;

use KlarnaPayment\Components\Client\Client;
use KlarnaPayment\Components\Client\Hydrator\Request\Test\TestRequestHydratorInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    public function __construct(
        Client $client,
        LoggerInterface $logger,
        TestRequestHydratorInterface $requestHydrator
    ) {
        $this->client          = $client;
        $this->logger          = $logger;
        $this->requestHydrator = $requestHydrator;
    }

    /**
     * @Route("/api/v{version}/_action/klarna_payment/validate-credentials", name="api.action.klarna_payment.validate.credentials", methods={"POST"})
     */
    public function validateCredentials(RequestDataBag $dataBag, Context $context): JsonResponse
    {
        $liveError = false;
        $testError = false;

        $liveCredentialsValid = $this->validate(
            $dataBag->get('apiUsername') ?? '',
            $dataBag->get('apiPassword') ?? '',
            $dataBag->get('salesChannel'),
            $context
        );

        if (!$liveCredentialsValid) {
            $liveError = true;
        }

        if (!empty($dataBag->get('testMode'))) {
            $testCredentialsValid = $this->validate(
                $dataBag->get('testApiUsername') ?? '',
                $dataBag->get('testApiPassword') ?? '',
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

    private function validate(string $username, string $password, ?string $salesChannel, Context $context): bool
    {
        $request  = $this->requestHydrator->hydrate($username, $password, $salesChannel);
        $response = $this->client->request($request, $context);

        $status = $response->getHttpStatus() === 404 && $response->getResponse()['error_code'] === 'NO_SUCH_ORDER';

        $this->logger->info('klarna plugin credentials validated', [
            'success'      => $status,
            'salesChannel' => $salesChannel ?? 'all',
        ]);

        return $status;
    }
}
