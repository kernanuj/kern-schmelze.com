<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Validator;

use KlarnaPayment\Components\ConfigReader\ConfigReaderInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Shopware\Core\System\StateMachine\StateMachineEntity;

class OrderTransitionChangeValidator
{
    public const STATUS_ENTITIES = [
        'orderStatus'    => OrderDefinition::ENTITY_NAME,
        'deliveryStatus' => OrderDeliveryDefinition::ENTITY_NAME,
    ];

    public const CAPTURE_SETTING_KEYS = [
        'orderStatus'    => 'captureOrderStatus',
        'deliveryStatus' => 'captureDeliveryStatus',
    ];

    public const REFUND_SETTING_KEYS = [
        'orderStatus'    => 'refundOrderStatus',
        'deliveryStatus' => 'refundDeliveryStatus',
    ];

    /** @var ConfigReaderInterface */
    private $configReader;

    /** @var EntityRepositoryInterface */
    private $stateMachineStateRepository;

    public function __construct(
        ConfigReaderInterface $configReader,
        EntityRepositoryInterface $stateMachineStateRepository
    ) {
        $this->configReader                = $configReader;
        $this->stateMachineStateRepository = $stateMachineStateRepository;
    }

    public function isAutomaticCapture(StateMachineTransitionEvent $transitionEvent, string $salesChannelId): bool
    {
        $config = $this->configReader->read($salesChannelId);
        $type   = $config->get('automaticCapture');

        if (!$this->hasDefinedType($type, $transitionEvent->getEntityName())) {
            return false;
        }

        if (!$this->isCorrectStateTransition(
            $config->get(self::CAPTURE_SETTING_KEYS[$type]),
            $transitionEvent->getToPlace()->getTechnicalName(),
            $transitionEvent->getContext())
        ) {
            return false;
        }

        return true;
    }

    public function isAutomaticRefund(StateMachineTransitionEvent $transitionEvent, string $salesChannelId): bool
    {
        $config = $this->configReader->read($salesChannelId);
        $type   = $config->get('automaticRefund');

        if (!$this->hasDefinedType($type, $transitionEvent->getEntityName())) {
            return false;
        }

        if (!$this->isCorrectStateTransition(
            $config->get(self::REFUND_SETTING_KEYS[$type]),
            $transitionEvent->getToPlace()->getTechnicalName(),
            $transitionEvent->getContext())
        ) {
            return false;
        }

        return true;
    }

    private function hasDefinedType(string $type, string $entityName): bool
    {
        if (!array_key_exists(($type), self::STATUS_ENTITIES)) {
            return false;
        }

        if (self::STATUS_ENTITIES[$type] !== $entityName) {
            return false;
        }

        return true;
    }

    private function isCorrectStateTransition(string $typeUuid, string $technicalName, Context $context): bool
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $typeUuid));

        $stateMachineSearchResult = $this->stateMachineStateRepository->search($criteria, $context);

        if ($stateMachineSearchResult->count() <= 0) {
            return false;
        }

        $stateMachineSearchResultElement = $stateMachineSearchResult->first();

        if (!$stateMachineSearchResultElement) {
            return false;
        }

        /** @var StateMachineEntity $stateMachineSearchResultElement */
        if ($stateMachineSearchResultElement->getTechnicalName() !== $technicalName) {
            return false;
        }

        return true;
    }
}
