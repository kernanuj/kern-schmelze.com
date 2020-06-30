<?php declare(strict_types=1);

namespace InvMixerProduct\Repository;


use Shopware\Core\Content\Product\ProductEntity as SubjectEntity;
use InvMixerProduct\Exception\EntityNotFoundException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

/**
 * Class ProductRepository
 * @package InvMixerProduct\Repository
 */
class ProductRepository
{

    /**
     * @var EntityRepositoryInterface
     */
    private $dalEntityRepository;

    /**
     * MixEntityRepository constructor.
     * @param EntityRepositoryInterface $dalEntityRepository
     */
    public function __construct(EntityRepositoryInterface $dalEntityRepository)
    {
        $this->dalEntityRepository = $dalEntityRepository;
    }

    /**
     * @param string $id
     * @param Context $context
     * @return SubjectEntity
     * @throws EntityNotFoundException
     */
    public function findOneById(string $id, Context $context): SubjectEntity
    {
        $found = $this->dalEntityRepository->search(
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
     * @param Context $context
     * @return SubjectEntity
     * @throws EntityNotFoundException
     */
    public function findOneByProductNumber(string $productNumber, Context $context): SubjectEntity {
        $found = $this->dalEntityRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('productNumber', $productNumber)),
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
     * @param Context $context
     * @return SubjectEntity
     * @throws EntityNotFoundException
     */
    public function mustFindOneEligibleForMixById(string $id, Context $context): SubjectEntity
    {
        $found = $this->dalEntityRepository->search(
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

        $this->assertIsProductEligibleForMix($found, $context);

        return $found;
    }

    /**
     * @todo add further checks if a product is actually a product that can be used for a mix; ie. by checking attributes or categories
     *
     * @param SubjectEntity $productEntity
     * @param Context $context
     *
     * @return $this
     */
    public function assertIsProductEligibleForMix(SubjectEntity $productEntity, Context $context) :self {

        return $this;
    }

}
