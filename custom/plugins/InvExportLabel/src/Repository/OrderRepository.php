<?php declare(strict_types=1);


namespace InvExportLabel\Repository;


use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;

/**
 * Class OrderRepository
 * @package InvExportLabel\Repository
 */
class OrderRepository
{

    /**
     * @var EntityRepositoryInterface
     */
    private $entityRepository;

    /**
     * OrderRepository constructor.
     * @param EntityRepositoryInterface $entityRepository
     */
    public function __construct(EntityRepositoryInterface $entityRepository)
    {
        $this->entityRepository = $entityRepository;
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

        return $this->entityRepository->search($criteria, $context)->getIds();
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
        $collection = $this->entityRepository->search($criteria, $context)->getEntities();

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
            'currency',
        ];
    }


}
