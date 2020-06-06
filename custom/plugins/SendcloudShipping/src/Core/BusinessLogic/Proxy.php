<?php

namespace Sendcloud\Shipping\Core\BusinessLogic;

use Sendcloud\Shipping\Core\BusinessLogic\DTO\IntegrationDTO;
use Sendcloud\Shipping\Core\BusinessLogic\DTO\ShipmentDTO;
use Sendcloud\Shipping\Core\BusinessLogic\DTO\ShipmentResponseDTO;
use Sendcloud\Shipping\Core\BusinessLogic\Interfaces\Configuration;
use Sendcloud\Shipping\Core\BusinessLogic\Interfaces\Proxy as ProxyInterface;
use Sendcloud\Shipping\Core\Infrastructure\Interfaces\Required\HttpClient;
use Sendcloud\Shipping\Core\Infrastructure\Logger\LogData;
use Sendcloud\Shipping\Core\Infrastructure\Logger\Logger;
use Sendcloud\Shipping\Core\Infrastructure\ServiceRegister;
use Sendcloud\Shipping\Core\Infrastructure\Utility\Exceptions\HttpAuthenticationException;
use Sendcloud\Shipping\Core\Infrastructure\Utility\Exceptions\HttpBatchSizeTooBigException;
use Sendcloud\Shipping\Core\Infrastructure\Utility\Exceptions\HttpCommunicationException;
use Sendcloud\Shipping\Core\Infrastructure\Utility\Exceptions\HttpRequestException;
use Sendcloud\Shipping\Core\Infrastructure\Utility\HttpResponse;

/**
 * Class Proxy
 * @package Sendcloud\Shipping\Core\BusinessLogic
 */
class Proxy implements ProxyInterface
{
    const HTTP_STATUS_CODE_UNAUTHORIZED = 401;
    const HTTP_STATUS_CODE_FORBIDDEN = 403;
    const HTTP_STATUS_CODE_NOT_SUCCESSFUL_FOR_DEFINED_BATCH_SIZE = 413;
    const BASE_URL = 'https://panel.sendcloud.sc/api/';
    const API_VERSION = 'v2/';

    /**
     * @var ProxyTransformer
     */
    protected $transformer;

    /**
     * @var Configuration
     */
    protected $configService;

    /**
     * @var HttpClient
     */
    private $client;

    /**
     * Proxy constructor.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $this->configService = ServiceRegister::getService(Configuration::CLASS_NAME);
        $this->transformer = ServiceRegister::getService(ProxyTransformer::CLASS_NAME);
        $this->client = ServiceRegister::getService(HttpClient::CLASS_NAME);
    }

    /**
     * Returns an integration by its id
     *
     * @param int $id
     *
     * @return DTO\IntegrationDTO
     *
     * @throws HttpAuthenticationException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     */
    public function getIntegrationById($id)
    {
        $response = $this->call('GET', 'integrations/' . $id);

        return $this->transformer->transformIntegrationResponse($response);
    }

    /**
     * Updates integration data
     *
     * @param IntegrationDTO $integrationDTO
     *
     * @throws HttpAuthenticationException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     */
    public function updateIntegration(IntegrationDTO $integrationDTO)
    {
        $dataForUpdate = $this->transformer->transformIntegration($integrationDTO);

        $this->call('PUT', "integrations/{$integrationDTO->getId()}", $dataForUpdate);
    }

    /**
     * Sends a log to SendCloud API asynchronously
     *
     * @param LogData $logData
     *
     * @throws HttpAuthenticationException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     */
    public function createLog(LogData $logData)
    {
        $this->callAsync('POST', 'logs', $this->transformer->transformLogData($logData));
    }

    /**
     * Sends a batch of logs to SendCloud API asynchronously
     *
     * @param array $logBatch
     *
     * @throws HttpAuthenticationException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     */
    public function createLogBatch(array $logBatch)
    {
        $this->callAsync('POST', 'logs/batch', $this->transformer->transformLogBatch($logBatch));
    }

    /**
     * Return an object of order for passed external id
     *
     * @param string $orderId
     *
     * @return DTO\ShipmentDTO
     *
     * @throws HttpAuthenticationException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     */
    public function getOrderByExternalId($orderId)
    {
        $integrationId = $this->configService->getIntegrationId();
        $response = $this->call('GET', "integrations/$integrationId/shipments?external_order_ids=$orderId");

        $shipments = $this->transformer->transformShipmentsResponse($response);

        return empty($shipments) ? null : $shipments[0];
    }

    /**
     * Returns parcel by its id
     *
     * @param int $parcelId
     *
     * @return array
     * @throws HttpAuthenticationException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     */
    public function getParcelById($parcelId)
    {
        $response = $this->call('GET', "parcels/$parcelId");

        return $this->transformer->transformParcel($response);
    }

    /**
     * Cancel parcel by id
     *
     * @param string $orderId
     * @param string|null $shipmentId
     *
     * @throws HttpAuthenticationException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     */
    public function cancelOrderById($orderId, $shipmentId = null)
    {
        $integrationId = $this->configService->getIntegrationId();
        $payload = array(
            'external_order_id' => $orderId,
            'external_shipment_id' => !empty($shipmentId) ? $shipmentId : '',
        );

        $this->call('POST', "integrations/$integrationId/shipments/delete", $payload);
    }

    /**
     * Makes shipment upsert call to SendCloud
     *
     * @param ShipmentDTO[] $shipmentDTOs
     *
     * @return ShipmentResponseDTO[]
     *
     * @throws HttpAuthenticationException
     * @throws HttpBatchSizeTooBigException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     */
    public function ordersMassUpdate(array $shipmentDTOs)
    {
        $orders = $this->transformer->transformShipments($shipmentDTOs);

        try {
            $endpoint = "integrations/{$this->configService->getIntegrationId()}/shipments";
            $response = $this->call('POST', $endpoint, $orders);
            $this->checkMassUpdateRequestSuccess($response, $shipmentDTOs);
        } catch (HttpRequestException $ex) {
            $batchSize = count($shipmentDTOs);
            $this->checkMassUpdateBatchSizeValidity($ex, $batchSize);

            throw $ex;
        }

        return $this->transformer->transformShipmentUpsertResponse($response);
    }

    /**
     * Call http client
     *
     * @param string $method HTTP method (GET, POST, PUT, etc.)
     * @param string $endpoint Endpoint resource on remote API
     * @param array $body Request payload body
     * @param string $publicKey
     * @param string $secretKey
     *
     * @return HttpResponse
     * @throws HttpAuthenticationException
     * @throws HttpCommunicationException
     * @throws HttpRequestException
     */
    public function call($method, $endpoint, array $body = array(), $publicKey = '', $secretKey = '')
    {
        $this->checkCredentials($publicKey, $secretKey);

        $bodyStringToSend = '';
        if (in_array(strtoupper($method), array('POST', 'PUT'))) {
            $bodyStringToSend = json_encode($body);
        }

        $response = $this->client->request(
            $method,
            static::BASE_URL . static::API_VERSION . ltrim($endpoint, '/'),
            $this->getHeaders($publicKey, $secretKey),
            $bodyStringToSend
        );

        $this->validateResponse($response);

        return $response;
    }

    /**
     * Call http client asynchronously
     *
     * @param string $method HTTP method (GET, POST, PUT, etc.)
     * @param string $endpoint Endpoint resource on remote API
     * @param array $body Request payload body
     * @param string $publicKey
     * @param string $secretKey
     *
     * @throws HttpCommunicationException
     */
    public function callAsync($method, $endpoint, $body = array(), $publicKey = '', $secretKey = '')
    {
        $this->checkCredentials($publicKey, $secretKey);

        $bodyStringToSend = '';
        if (in_array(strtoupper($method), array('POST', 'PUT'))) {
            $bodyStringToSend = json_encode($body);
        }

        $this->client->requestAsync(
            $method,
            static::BASE_URL . static::API_VERSION . ltrim($endpoint, '/'),
            $this->getHeaders($publicKey, $secretKey),
            $bodyStringToSend
        );
    }

    /**
     * Validate response
     *
     * @param HttpResponse $response
     *
     * @throws HttpAuthenticationException
     * @throws HttpRequestException
     */
    protected function validateResponse(HttpResponse $response)
    {
        if (!$response->isSuccessful()) {
            $httpCode = $response->getStatus();
            $message = $body = $response->getBody();
            $error = json_decode($body, true);
            if (is_array($error)) {
                if (isset($error['error']['message'])) {
                    $message = $error['error']['message'];
                }

                if (isset($error['error']['code'])) {
                    $httpCode = $error['error']['code'];
                }
            }

            Logger::logInfo($message);
            if ($httpCode === self::HTTP_STATUS_CODE_UNAUTHORIZED
                || $httpCode === self::HTTP_STATUS_CODE_FORBIDDEN
            ) {
                throw new HttpAuthenticationException($message, $httpCode);
            }

            throw new HttpRequestException($message, $httpCode);
        }
    }

    /**
     * @param HttpResponse $response
     * @param array $orderDTOs
     *
     * @throws HttpRequestException
     */
    private function checkMassUpdateRequestSuccess(HttpResponse $response, array $orderDTOs)
    {
        $responseBody = json_decode($response->getBody(), true);
        if ($responseBody === false) {
            $firstOrder = !empty($orderDTOs[0]) ? $orderDTOs[0]->getOrderEntity()->getOrderNumber() : '';
            $message = 'Upsert of orders not done for batch starting from order id ' . $firstOrder . '.' .
                'Batch size is ' . count($orderDTOs) . '.';
            Logger::logError($message);

            throw new HttpRequestException($message);
        }
    }

    /**
     * @param HttpRequestException $ex
     * @param int $batchSize
     *
     * @throws HttpBatchSizeTooBigException
     */
    private function checkMassUpdateBatchSizeValidity($ex, $batchSize)
    {
        if ($ex->getCode() === self::HTTP_STATUS_CODE_NOT_SUCCESSFUL_FOR_DEFINED_BATCH_SIZE) {
            Logger::logInfo('Upsert of orders not done for batch size ' . $batchSize . '.');

            throw new HttpBatchSizeTooBigException('Batch size ' . $batchSize . ' too big for uspert');
        }
    }

    /**
     * Checks if provided keys are empty, if they are they are fetched from configuration
     *
     * @param string $publicKey
     * @param string $secretKey
     *
     * @throws HttpCommunicationException
     */
    private function checkCredentials(&$publicKey, &$secretKey)
    {
        if (empty($publicKey) || empty($secretKey)) {
            $publicKey = $this->configService->getPublicKey();
            $secretKey = $this->configService->getSecretKey();
        }

        if (empty($publicKey) || empty($secretKey)) {
            $errorMessage = 'Missing credentials. Public and secret keys are not set in Configuration service.';
            Logger::logError($errorMessage);
            throw new HttpCommunicationException($errorMessage);
        }
    }

    /**
     * Returns headers together with authorization entry
     *
     * @param string $publicKey
     * @param string $secretKey
     *
     * @return array
     */
    private function getHeaders($publicKey, $secretKey)
    {
        return array(
            'accept' => 'Accept: application/json',
            'content' => 'Content-Type: application/json',
            'token' => 'Authorization: Basic ' . base64_encode("$publicKey:$secretKey"),
        );
    }
}
