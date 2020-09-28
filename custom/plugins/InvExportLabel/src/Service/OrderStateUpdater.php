<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\ExportResult;
use InvExportLabel\Value\SourceCollection;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionEntity;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;

/**
 * Class OrderSourceMarker
 * @package InvExportLabel\Service
 */
class OrderStateUpdater implements OrderActionInterface
{


    /**
     * @var StateMachineRegistry
     */
    private $stateMachineRegistry;

    /**
     * OrderStateUpdater constructor.
     * @param StateMachineRegistry $stateMachineRegistry
     */
    public function __construct(StateMachineRegistry $stateMachineRegistry)
    {
        $this->stateMachineRegistry = $stateMachineRegistry;
    }


    /**
     * @param ExportRequestConfiguration $exportRequestConfiguration
     * @param SourceCollection $sourceCollection
     * @param ExportResult $exportResult
     */
    public function run(
        ExportRequestConfiguration $exportRequestConfiguration,
        SourceCollection $sourceCollection,
        ExportResult $exportResult
    ) {

        if(true !== $exportRequestConfiguration->isUpdateStatusAfter()){
            $exportResult->addLog('Skipping order status update');
            return;
        }

        $context = new Context(new SystemSource());
        foreach ($sourceCollection->getOrderCollection() as $order) {
            try {
                $availableTransitions = $this->stateMachineRegistry->getAvailableTransitions(
                    OrderDefinition::ENTITY_NAME,
                    $order->getId(),
                    'stateId',
                    $context
                );
                $availableTransitionNames = [];
                /** @var StateMachineTransitionEntity $availableTransition */
                foreach ($availableTransitions as $availableTransition) {
                    $availableTransitionNames[] = $availableTransition->getActionName();
                }

                if (!in_array($exportRequestConfiguration->getTransitionAfterSendout(), $availableTransitionNames)) {
                    throw new \RuntimeException('could not perform transition' . $exportRequestConfiguration->getTransitionAfterSendout());
                }
                $this->stateMachineRegistry->transition(
                    new Transition(
                        OrderDefinition::ENTITY_NAME,
                        $order->getId(),
                        $exportRequestConfiguration->getTransitionAfterSendout(),
                        'stateId'
                    ),
                    $context
                );

                $exportResult->addLog(
                    sprintf(
                        'Update order status for order #%s',
                        $order->getOrderNumber()
                    )
                );

            } catch (\Throwable $e) {
                $exportResult->addLog(
                    sprintf(
                        'Failed updating state for order %s with %s',
                        $order->getOrderNumber(),
                        $e->getMessage()
                    )
                );
            }
        }
    }
}
