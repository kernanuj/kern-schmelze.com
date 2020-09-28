<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use InvExportLabel\Repository\OrderRepository;
use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\OrderStateCombination;
use InvExportLabel\Value\SourceCollection;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Throwable;

/**
 * Class OrderSourceProvider
 * @package InvExportLabel\Service
 */
class  OrderSourceProvider implements SourceProviderInterface
{

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var TypeInstanceRegistry
     */
    private $typeInstanceRegistry;

    /**
     * OrderSourceProvider constructor.
     * @param OrderRepository $orderRepository
     * @param TypeInstanceRegistry $typeInstanceRegistry
     */
    public function __construct(OrderRepository $orderRepository, TypeInstanceRegistry $typeInstanceRegistry)
    {
        $this->orderRepository = $orderRepository;
        $this->typeInstanceRegistry = $typeInstanceRegistry;
    }

    /**
     * @inheritDoc
     */
    public function fetchSourceCollection(ExportRequestConfiguration $configuration): SourceCollection
    {
        $collection = new SourceCollection();

        $typeInstance = $this->typeInstanceRegistry->forType($configuration->getType());

        $orderEntityCollection = $this->loadMatchingOrders(
            $configuration,
            Context::createDefaultContext()
        );

        $collection->setOrderCollection($orderEntityCollection);

        $matchingOrderLineItems = $typeInstance->extractOrderLineItems($orderEntityCollection);

        foreach ($matchingOrderLineItems as $matchingOrderLineItem) {
            $converted = $typeInstance->convertOrderLineItemToSourceItem(
                $matchingOrderLineItem,
                $configuration
            );
            for ($i = 0; $i < $matchingOrderLineItem->getQuantity(); $i++ ) {
                $collection->addItem(
                    $converted
                );
            }
        }

        return $collection;
    }

    /**
     * @param ExportRequestConfiguration $configuration
     * @param Context $context
     * @return OrderCollection
     */
    private function loadMatchingOrders(ExportRequestConfiguration $configuration, Context $context): OrderCollection
    {
//@todo: eventually filter orders for state already on repository level
        $unfilteredOrders = $this->orderRepository->getOrdersWithStateChangeInDateRange(
            $configuration->getSourceFilterDefinition()->getOrderedAtFrom(),
            $configuration->getSourceFilterDefinition()->getOrderedAtTo(),
            $context
        );

        return $unfilteredOrders->filter(function (OrderEntity $orderEntity) use ($configuration) {
            try {
                $orderState = null;
                $orderTransactionState = null;
                $orderDeliveryState = null;

                $orderState = $orderEntity->getStateMachineState()->getTechnicalName();

                if ($orderEntity->getTransactions()) {
                    $orderTransactionState = $orderEntity->getTransactions()->last()->getStateMachineState()->getTechnicalName();
                }

                if ($orderEntity->getDeliveries()) {
                    $orderDeliveryState = $orderEntity->getDeliveries()->last()->getStateMachineState()->getTechnicalName();
                }

                $currentStateCombination = new OrderStateCombination(
                    $orderState,
                    $orderTransactionState,
                    $orderDeliveryState
                );

                if (true !== $configuration->getSourceFilterDefinition()->getOrderStateCombinationCollection()->hasOneMatching(
                        $currentStateCombination
                    )) {
                    return false;
                }


            } catch (Throwable $e) {
                trigger_error('could not correctly determine if order matches', E_USER_NOTICE);
                return false;
            }

            return true;
        });


    }
}
