<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\EventListener;

use KlarnaPayment\Components\Client\ClientInterface;
use KlarnaPayment\Components\Client\Hydrator\Request\UpdateAddress\UpdateAddressRequestHydratorInterface;
use KlarnaPayment\Components\Client\Hydrator\Request\UpdateOrder\UpdateOrderRequestHydratorInterface;
use KlarnaPayment\Components\Helper\OrderFetcherInterface;
use KlarnaPayment\Components\Helper\OrderValidator\OrderValidatorInterface;
use KlarnaPayment\Components\Helper\RequestHasherInterface;
use KlarnaPayment\Core\Framework\ContextScope;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PostWriteValidationEvent;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrderChangeEventListener implements EventSubscriberInterface
{
    /** @var OrderFetcherInterface */
    private $orderFetcher;

    /** @var UpdateAddressRequestHydratorInterface */
    private $addressRequestHydrator;

    /** @var UpdateOrderRequestHydratorInterface */
    private $orderRequestHydrator;

    /** @var ClientInterface */
    private $client;

    /** @var EntityRepositoryInterface */
    private $orderRepository;

    /** @var TranslatorInterface */
    private $translator;

    /** @var RequestHasherInterface */
    private $requestHasher;

    /** @var OrderValidatorInterface */
    private $orderValidator;

    public function __construct(
        OrderFetcherInterface $orderFetcher,
        UpdateAddressRequestHydratorInterface $addressRequestHydrator,
        UpdateOrderRequestHydratorInterface $orderRequestHydrator,
        ClientInterface $client,
        EntityRepositoryInterface $orderRepository,
        TranslatorInterface $translator,
        RequestHasherInterface $requestHasher,
        OrderValidatorInterface $orderValidator
    ) {
        $this->orderFetcher           = $orderFetcher;
        $this->addressRequestHydrator = $addressRequestHydrator;
        $this->orderRequestHydrator   = $orderRequestHydrator;
        $this->client                 = $client;
        $this->orderRepository        = $orderRepository;
        $this->translator             = $translator;
        $this->requestHasher          = $requestHasher;
        $this->orderValidator         = $orderValidator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostWriteValidationEvent::class => 'validateKlarnaOrder',
        ];
    }

    /**
     * @see \KlarnaPayment\Components\Controller\Administration\OrderUpdateController::update Change accordingly to keep functionality synchronized
     */
    public function validateKlarnaOrder(PostWriteValidationEvent $event): void
    {
        if ($event->getContext()->getScope() === ContextScope::INTERNAL_SCOPE) {
            // only check user generated changes
            return;
        }

        if ($event->getContext()->getVersionId() !== Defaults::LIVE_VERSION) {
            // No live data change, just draft versions
            return;
        }

        $order = $this->getOrderFromWriteCommands($event);

        if (null === $order || !$this->orderValidator->isKlarnaOrder($order)) {
            return;
        }

        $this->validateOrderAddress($order, $event);
        $this->validateLineItems($order, $event);
    }

    private function validateLineItems(OrderEntity $orderEntity, PostWriteValidationEvent $event): void
    {
        $request = $this->orderRequestHydrator->hydrate($orderEntity, $event->getContext());

        $hash = $this->requestHasher->getHash($request);

        if (!empty($orderEntity->getCustomFields()['klarna_order_cart_hash'])) {
            $currentHash = $orderEntity->getCustomFields()['klarna_order_cart_hash'];

            if ($hash === $currentHash) {
                return;
            }
        }

        $response = $this->client->request($request, $event->getContext());

        if ($response->getHttpStatus() === 204) {
            $this->saveOrderCartHash($hash, $orderEntity, $event->getContext());

            return;
        }

        $violation = new ConstraintViolation(
            $this->translator->trans('KlarnaPayment.errorMessages.lineItemChangeDeclined'),
            '',
            [],
            '',
            '',
            ''
        );

        $violations = new ConstraintViolationList([$violation]);

        $event->getExceptions()->add(new WriteConstraintViolationException($violations));
    }

    private function validateOrderAddress(OrderEntity $orderEntity, PostWriteValidationEvent $event): void
    {
        $request = $this->addressRequestHydrator->hydrate($orderEntity, $event->getContext());

        $hash = $this->requestHasher->getHash($request);

        if (!empty($orderEntity->getCustomFields()['klarna_order_address_hash'])) {
            $currentHash = $orderEntity->getCustomFields()['klarna_order_address_hash'];

            if ($hash === $currentHash) {
                return;
            }
        }

        $response = $this->client->request($request, $event->getContext());

        if ($response->getHttpStatus() === 204) {
            $this->saveOrderAddressHash($hash, $orderEntity, $event->getContext());

            return;
        }

        $violation = new ConstraintViolation(
            $this->translator->trans('KlarnaPayment.errorMessages.addressChangeDeclined'),
            '',
            [],
            '',
            '',
            ''
        );

        $violations = new ConstraintViolationList([$violation]);

        $event->getExceptions()->add(new WriteConstraintViolationException($violations));
    }

    private function getOrderFromWriteCommands(PostWriteValidationEvent $event): ?OrderEntity
    {
        foreach ($event->getCommands() as $command) {
            if (!($command instanceof UpdateCommand)) {
                continue;
            }

            $primaryKeys = $command->getPrimaryKey();

            if (!array_key_exists('id', $primaryKeys) || empty($primaryKeys['id'])) {
                continue;
            }

            if ($command->getDefinition()->getClass() === OrderAddressDefinition::class) {
                return $this->orderFetcher->getOrderFromOrderAddress($primaryKeys['id'], $event->getContext());
            }

            if ($command->getDefinition()->getClass() === OrderLineItemDefinition::class) {
                return $this->orderFetcher->getOrderFromOrderLineItem($primaryKeys['id'], $event->getContext());
            }

            if ($command->getDefinition()->getClass() === OrderDefinition::class) {
                return $this->orderFetcher->getOrderFromOrder($primaryKeys['id'], $event->getContext());
            }
        }

        return null;
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
