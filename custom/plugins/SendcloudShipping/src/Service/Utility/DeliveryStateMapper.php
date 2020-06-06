<?php

namespace Sendcloud\Shipping\Service\Utility;

use Sendcloud\Shipping\Entity\StateMachine\StateMachineRepository;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;

/**
 * Class DeliveryStatusMapper
 *
 * @package Sendcloud\Shipping\Service\Utility
 */
class DeliveryStateMapper
{
    public const SENDCLOUD_STATUS_READY_TO_SEND = 1000;
    public const SENDCLOUD_STATUS_ANNOUNCED = 1;
    public const SENDCLOUD_STATUS_ANNOUNCED_NOT_COLLECTED = 13;
    public const SENDCLOUD_STATUS_DELIVERED = 11;
    public const SENDCLOUD_STATUS_COLLECTED_BY_CUSTOMER = 93;
    public const SENDCLOUD_STATUS_CANCELLED_UPSTREAM = 1998;
    public const SENDCLOUD_STATUS_CANCELLATION_REQUESTED = 1999;
    public const SENDCLOUD_STATUS_CANCELLED = 2000;
    public const SENDCLOUD_STATUS_CANCELLATION_SUBMITTING = 2001;

    /**
     * @var StateMachineRepository
     */
    private $stateMachineRepository;

    private static $statusMap = [
        self::SENDCLOUD_STATUS_READY_TO_SEND => OrderDeliveryStates::STATE_SHIPPED,
        self::SENDCLOUD_STATUS_ANNOUNCED => OrderDeliveryStates::STATE_SHIPPED,
        self::SENDCLOUD_STATUS_ANNOUNCED_NOT_COLLECTED => OrderDeliveryStates::STATE_SHIPPED,
        self::SENDCLOUD_STATUS_DELIVERED => OrderDeliveryStates::STATE_SHIPPED,
        self::SENDCLOUD_STATUS_COLLECTED_BY_CUSTOMER => OrderDeliveryStates::STATE_SHIPPED,
        self::SENDCLOUD_STATUS_CANCELLED_UPSTREAM => OrderDeliveryStates::STATE_CANCELLED,
        self::SENDCLOUD_STATUS_CANCELLATION_REQUESTED => OrderDeliveryStates::STATE_CANCELLED,
        self::SENDCLOUD_STATUS_CANCELLED => OrderDeliveryStates::STATE_CANCELLED,
        self::SENDCLOUD_STATUS_CANCELLATION_SUBMITTING => OrderDeliveryStates::STATE_CANCELLED,
    ];

    /**
     * DeliveryStatusMapper constructor.
     *
     * @param StateMachineRepository $stateMachineRepository
     */
    public function __construct(StateMachineRepository $stateMachineRepository)
    {
        $this->stateMachineRepository = $stateMachineRepository;
    }


    /**
     * Return order delivery state id
     *
     * @param int $sendcloudStatusId
     *
     * @return string|null
     * @throws InconsistentCriteriaIdsException
     */
    public function getDeliveryStatusStateId(int $sendcloudStatusId): ?string
    {

        $state = $this->stateMachineRepository->getOrderDeliveryState($this->getDeliveryState($sendcloudStatusId), Context::createDefaultContext());

        return $state ? $state->getId() : null;
    }

    /**
     * Return order delivery state
     *
     * @param int $sendcloudStatusId
     *
     * @return string
     */
    private function getDeliveryState(int $sendcloudStatusId): string
    {
        return array_key_exists($sendcloudStatusId, self::$statusMap) ? self::$statusMap[$sendcloudStatusId] : OrderDeliveryStates::STATE_OPEN;
    }
}
