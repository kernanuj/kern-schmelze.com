<?php declare(strict_types=1);

namespace InvMixerProduct\Repository;


use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Tag\TagEntity as SubjectEntity;

/**
 * Class TagRepository
 * @package InvMixerProduct\Repository
 */
class TagRepository
{

    /**
     * @var EntityRepositoryInterface
     */
    private $dalEntityRepository;

    /**
     * TagRepository constructor.
     * @param EntityRepositoryInterface $dalEntityRepository
     */
    public function __construct(EntityRepositoryInterface $dalEntityRepository)
    {
        $this->dalEntityRepository = $dalEntityRepository;
    }


    /**
     * @param string $name
     * @param Context $context
     * @return SubjectEntity[]|EntitySearchResult
     */
    public function findAllByName(string $name, Context $context)
    {
        return $this->dalEntityRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('name', $name)),
            $context
        );
    }

    /**
     * @param string $name
     * @param Context $context
     * @return SubjectEntity
     */
    public function findOneByName(string $name, Context $context): SubjectEntity
    {
        return $this->dalEntityRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('name', $name)),
            $context
        )->first();
    }
}
