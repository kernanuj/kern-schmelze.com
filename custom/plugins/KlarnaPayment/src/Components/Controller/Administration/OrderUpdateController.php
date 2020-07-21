<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Controller\Administration;

use KlarnaPayment\Components\Client\Client;
use KlarnaPayment\Components\Client\Hydrator\Request\UpdateAddress\UpdateAddressRequestHydratorInterface;
use KlarnaPayment\Components\Client\Hydrator\Request\UpdateOrder\UpdateOrderRequestHydratorInterface;
use KlarnaPayment\Components\Helper\OrderFetcherInterface;
use KlarnaPayment\Components\Helper\OrderValidator\OrderValidatorInterface;
use KlarnaPayment\Components\Helper\RequestHasherInterface;
use KlarnaPayment\Core\Framework\ContextScope;
use KlarnaPayment\Exception\OrderUpdateDeniedException;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Uuid\Exception\InvalidUuidException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class OrderUpdateController extends AbstractController
{
    /** @var Client */
    private $client;

    /** @var EntityRepositoryInterface */
    private $orderRepository;

    /** @var UpdateAddressRequestHydratorInterface */
    private $updateAddressRequestHydrator;

    /** @var UpdateOrderRequestHydratorInterface */
    private $updateOrderRequestHydrator;

    /** @var OrderFetcherInterface */
    private $orderFetcher;

    /** @var RequestHasherInterface */
    private $requestHasher;

    /** @var OrderValidatorInterface */
    private $orderValidator;

    public function __construct(
        Client $client,
        EntityRepositoryInterface $orderRepository,
        UpdateAddressRequestHydratorInterface $updateAddressRequestHydrator,
        UpdateOrderRequestHydratorInterface $updateOrderRequestHydrator,
        OrderFetcherInterface $orderFetcher,
        RequestHasherInterface $requestHasher,
        OrderValidatorInterface $orderValidator
    ) {
        $this->client                       = $client;
        $this->orderRepository              = $orderRepository;
        $this->updateAddressRequestHydrator = $updateAddressRequestHydrator;
        $this->updateOrderRequestHydrator   = $updateOrderRequestHydrator;
        $this->orderFetcher                 = $orderFetcher;
        $this->requestHasher                = $requestHasher;
        $this->orderValidator               = $orderValidator;
    }

    /**
     * @Route("/api/v{version}/_action/klarna_payment/update_order", name="api.action.klarna_payment.order_update.update", methods={"POST"})
     *
     * @throws OrderUpdateDeniedException
     *
     * @see \KlarnaPayment\Components\EventListener\OrderChangeEventListener::validateKlarnaOrder Change accordingly to keep functionality synchronized
     */
    public function update(RequestDataBag $dataBag, Context $context): JsonResponse
    {
        $orderId = $dataBag->get('orderId', '');

        try {
            $orderEntity = $this->orderFetcher->getOrderFromOrder(Uuid::fromHexToBytes($orderId), $context);
        } catch (InvalidUuidException $e) {
            return new JsonResponse(['status' => 'success'], 200);
        }

        if (!$orderEntity) {
            return new JsonResponse(['status' => 'success'], 200);
        }

        if (!$this->orderValidator->isKlarnaOrder($orderEntity)) {
            return new JsonResponse(['status' => 'success'], 200);
        }

        if (!$this->validateLineItems($orderEntity, $context) || !$this->validateOrderAddress($orderEntity, $context)) {
            throw new OrderUpdateDeniedException($orderId);
        }

        return new JsonResponse(['status' => 'success'], 200);
    }

    private function validateLineItems(OrderEntity $orderEntity, Context $context): bool
    {
        $request = $this->updateOrderRequestHydrator->hydrate($orderEntity, $context);

        $hash = $this->requestHasher->getHash($request);

        if (!empty($orderEntity->getCustomFields()['klarna_order_cart_hash'])) {
            $currentHash = $orderEntity->getCustomFields()['klarna_order_cart_hash'];

            if ($hash === $currentHash) {
                return true;
            }
        }

        $response = $this->client->request($request, $context);

        if ($response->getHttpStatus() === 204) {
            $this->saveOrderCartHash($hash, $orderEntity, $context);

            return true;
        }

        return false;
    }

    private function validateOrderAddress(OrderEntity $orderEntity, Context $context): bool
    {
        $request = $this->updateAddressRequestHydrator->hydrate($orderEntity, $context);

        $hash = $this->requestHasher->getHash($request);

        if (!empty($orderEntity->getCustomFields()['klarna_order_address_hash'])) {
            $currentHash = $orderEntity->getCustomFields()['klarna_order_address_hash'];

            if ($hash === $currentHash) {
                return true;
            }
        }

        $response = $this->client->request($request, $context);

        if ($response->getHttpStatus() === 204) {
            $this->saveOrderAddressHash($hash, $orderEntity, $context);

            return true;
        }

        return false;
    }

    private function saveOrderAddressHash(string $hash, OrderEntity $orderEntity, Context $context): void
    {
        $customFields = $orderEntity->getCustomFields();

        $customFields['klarna_order_address_hash'] = $hash;

        $update = [
            'id'           => $orderEntity->getId(),
            'customFields' => $customFields,
        ];

        $context->scope(ContextScope::INTERNAL_SCOPE, function (Context $context) use ($update): void {
            $this->orderRepository->upsert([$update], $context);
        });
    }

    private function saveOrderCartHash(string $hash, OrderEntity $orderEntity, Context $context): void
    {
        $customFields = $orderEntity->getCustomFields();

        $customFields['klarna_order_cart_hash'] = $hash;

        $update = [
            'id'           => $orderEntity->getId(),
            'customFields' => $customFields,
        ];

        $context->scope(ContextScope::INTERNAL_SCOPE, function (Context $context) use ($update): void {
            $this->orderRepository->upsert([$update], $context);
        });
    }
}
