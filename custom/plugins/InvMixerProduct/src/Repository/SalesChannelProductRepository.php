<?php declare(strict_types=1);

namespace InvMixerProduct\Repository;

use InvMixerProduct\Exception\EntityNotFoundException;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity as SubjectEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class SalesChannelProductRepository
 * @package InvMixerProduct\Repository
 */
class SalesChannelProductRepository
{

    /**
     * @var SalesChannelRepository
     */
    private $salesChannelRepository;

    /**
     * ProductRepository constructor.
     * @param SalesChannelRepository $salesChannelRepository
     */
    public function __construct(SalesChannelRepository $salesChannelRepository)
    {
        $this->salesChannelRepository = $salesChannelRepository;
    }

    /**
     * @param string $id
     * @param SalesChannelContext $context
     * @return SubjectEntity
     * @throws EntityNotFoundException
     */
    public function findOneById(string $id, SalesChannelContext $context): SubjectEntity
    {
        $found = $this->salesChannelRepository->search(
            (new Criteria(
                [$id]
            )),
            $context
        )->first();

        if (!$found || !$found instanceof SubjectEntity) {
            throw EntityNotFoundException::fromEntityAndIdentifier(
                SubjectEntity::class,
                $id
            );
        }

        return $found;
    }

    /**
     * @param string $productNumber
     * @param SalesChannelContext $context
     * @return SubjectEntity
     * @throws EntityNotFoundException
     */
    public function findOneByProductNumber(string $productNumber, SalesChannelContext $context): SubjectEntity
    {
        $found = $this->salesChannelRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('productNumber', $productNumber))
                ->addAssociation('prices')
                ->addAssociation('unit')
                ->addAssociation('cover')
            ,
            $context
        )->first();

        if (!$found || !$found instanceof SubjectEntity) {
            throw EntityNotFoundException::fromEntityAndIdentifier(
                SubjectEntity::class,
                $productNumber
            );
        }

        return $found;
    }

    /**
     * @param string $id
     * @param SalesChannelContext $context
     * @return SubjectEntity
     * @throws EntityNotFoundException
     */
    public function mustFindOneEligibleForMixById(string $id, SalesChannelContext $context): SubjectEntity
    {
        $found = $this->salesChannelRepository->search(
            (new Criteria(
                [$id]
            ))
                ->addAssociation('prices')
                ->addAssociation('unit')
                ->addAssociation('cover'),
            $context
        )->first();

        if (!$found || !$found instanceof SubjectEntity) {
            throw EntityNotFoundException::fromEntityAndIdentifier(
                SubjectEntity::class,
                $id
            );
        }

        return $found;
    }

}
