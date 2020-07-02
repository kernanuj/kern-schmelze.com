<?php

namespace Sendcloud\Shipping\Entity\ServicePoint;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

/**
 * Class ServicePointEntityRepository
 *
 * @package Sendcloud\Shipping\Entity\ServicePoint
 */
class ServicePointEntityRepository
{
    /**
     * @var EntityRepositoryInterface
     */
    private $baseRepository;

    /**
     * ServicePointEntityRepository constructor.
     *
     * @param EntityRepositoryInterface $baseRepository
     */
    public function __construct(EntityRepositoryInterface $baseRepository)
    {
        $this->baseRepository = $baseRepository;
    }

    /**
     * Saves service point for provided customer number
     *
     * @param string $customerNumber
     * @param array $servicePointInfo
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function saveServicePoint(string $customerNumber, array $servicePointInfo): void
    {
        $existingServicePoint = $this->getServicePointByCustomerNumber($customerNumber);
        $payload = [
            'customerNumber' => $customerNumber,
            'servicePointInfo' => json_encode($servicePointInfo),
        ];
        $context = Context::createDefaultContext();
        if ($existingServicePoint) {
            $payload['id'] = $existingServicePoint->getId();
            $this->baseRepository->update([$payload], $context);
        } else {
            $this->baseRepository->create([$payload], $context);
        }
    }

    /**
     * Returns service point by customer number
     *
     * @param string $customerNumber
     *
     * @return ServicePointEntity|null
     * @throws InconsistentCriteriaIdsException
     */
    public function getServicePointByCustomerNumber(string $customerNumber): ?ServicePointEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customerNumber', $customerNumber));

        return $this->baseRepository->search($criteria, Context::createDefaultContext())->first();
    }

    /**
     * Removes service point for provided customer number
     *
     * @param string $customerNumber
     *
     * @throws InconsistentCriteriaIdsException
     */
    public function deleteServicePointByCustomerNumber(string $customerNumber): void
    {
        $existingServicePoint = $this->getServicePointByCustomerNumber($customerNumber);
        if ($existingServicePoint) {
            $this->baseRepository->delete([['id' => $existingServicePoint->getId()]], Context::createDefaultContext());
        }
    }
}
