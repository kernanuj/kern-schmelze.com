<?php declare(strict_types=1);


namespace InvExportLabel\Repository;


use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineHistory\StateMachineHistoryEntity;

/**
 * Class OrderRepository
 * @package InvExportLabel\Repository
 */
class OrderRepository
{

    /**
     * @var EntityRepositoryInterface
     */
    private $baseOrderRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $baseStateMachineHistoryRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $baseOrderTransactionRepository;

    /**
     * OrderRepository constructor.
     * @param EntityRepositoryInterface $baseOrderRepository
     * @param EntityRepositoryInterface $baseStateMachineHistoryRepository
     * @param EntityRepositoryInterface $baseOrderTransactionRepository
     */
    public function __construct(
        EntityRepositoryInterface $baseOrderRepository,
        EntityRepositoryInterface $baseStateMachineHistoryRepository,
        EntityRepositoryInterface $baseOrderTransactionRepository
    ) {
        $this->baseOrderRepository = $baseOrderRepository;
        $this->baseStateMachineHistoryRepository = $baseStateMachineHistoryRepository;
        $this->baseOrderTransactionRepository = $baseOrderTransactionRepository;
    }


    /**
     * @param \DateTime $fromDate
     * @param \DateTime $toDate
     * @param Context $context
     * @return OrderCollection
     */
    public function getOrdersForDateRange(
        \DateTime $fromDate,
        \DateTime $toDate,
        Context $context
    ): OrderCollection {

        $ids = $this->getOrderIdsForDateRange(
            $fromDate,
            $toDate,
            $context
        );

        return $this->getOrders($ids, $context);
    }

    /**
     * @param \DateTime $fromDate
     * @param \DateTime $toDate
     * @param Context $context
     * @return array
     */
    public function getOrderIdsForDateRange(
        \DateTime $fromDate,
        \DateTime $toDate,
        Context $context

    ): array {
        $criteria = new Criteria();

        $criteria->addFilter(new RangeFilter('orderDateTime', ['gte' => $fromDate->format(DATE_ATOM)]));
        $criteria->addFilter(new RangeFilter('orderDateTime', ['lte' => $toDate->format(DATE_ATOM)]));

        return $this->baseOrderRepository->search($criteria, $context)->getIds();
    }

    /**
     * @param array $orderIds
     * @param Context $context
     * @return OrderCollection
     */
    public function getOrders(array $orderIds, Context $context): OrderCollection
    {
        $criteria = new Criteria($orderIds);
        $criteria->addAssociations($this->getAssociationsForOrder());

        /** @var OrderCollection $collection */
        $collection = $this->baseOrderRepository->search($criteria, $context)->getEntities();

        return $collection;
    }

    /**
     * Returns associations for order search
     *
     * @return array
     */
    private function getAssociationsForOrder(): array
    {
        return [
            'lineItems.product',
            'transactions',
            'deliveries',
            'currency',
        ];
    }

    /**
     * @param \DateTime $fromDate
     * @param \DateTime $toDate
     * @param Context $context
     * @return OrderCollection
     */
    public function getOrdersWithStateChangeInDateRange(
        \DateTime $fromDate,
        \DateTime $toDate,
        Context $context
    ): OrderCollection {

        $ids = $this->getOrderIdsFromOrderTransactions($fromDate, $toDate, $context);

        return $this->getOrders($ids, $context);
    }

    /**
     * @param \DateTime $fromDate
     * @param \DateTime $toDate
     * @param Context $context
     * @return array|int[]
     */
    private function getOrderTransactionIdsFromStateHistory(\DateTime $fromDate, \DateTime $toDate, Context $context): array
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('entityName', 'order_transaction'));
        $criteria->addFilter(new RangeFilter('createdAt', ['gte' => $fromDate->format(DATE_ATOM)]));
        $criteria->addFilter(new RangeFilter('createdAt', ['lte' => $toDate->format(DATE_ATOM)]));
        $transactions = $this->baseStateMachineHistoryRepository->search(
            $criteria,
            $context
        );

        $ids = array_map(function (StateMachineHistoryEntity $entity) {
            return $entity->getEntityId()['id'] ?? null;
        }, $transactions->getElements());

        return array_values(array_unique($ids));
    }

    /**
     * @param \DateTime $fromDate
     * @param \DateTime $toDate
     * @param Context $context
     * @return array|int[]
     */
    private function getOrderIdsFromOrderTransactions(\DateTime $fromDate, \DateTime $toDate, Context $context): array
    {

        $ids = $this->getOrderTransactionIdsFromStateHistory(
            $fromDate,
            $toDate,
            $context
        );

        $transactions = $this->baseOrderTransactionRepository->search(
            new Criteria($ids),
            $context
        );

        $ids = array_map(function (OrderTransactionEntity $entity) {
            return $entity->getOrderId();
        }, $transactions->getElements());

        return array_values(array_unique($ids));
    }


}
