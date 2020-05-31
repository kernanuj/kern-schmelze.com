<?php declare(strict_types=1);

namespace InvMixerProduct\Repository;


use InvMixerProduct\Entity\MixEntity as SubjectEntity;
use InvMixerProduct\Exception\EntityNotFoundException;
use InvMixerProduct\Struct\ContainerDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Class MixEntityRepository
 * @package InvMixerProduct\Repository
 */
class MixEntityRepository
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
     * @return SubjectEntity
     */
    public function create(): SubjectEntity
    {
        $entity = new SubjectEntity();
        $entity->setContainerDefinition(ContainerDefinition::aDefault());
        $entity->setId(Uuid::randomHex());

        return $entity;
    }

    /**
     * @param SubjectEntity $entity
     * @param Context $context
     */
    public function save(SubjectEntity $entity, Context $context)
    {
        $data = [
            'id' => $entity->getId(),
            'created_at' => $entity->getCreatedAt(),
            'updated_at' => $entity->getUpdatedAt(),
            'containerDefinition' => $entity->getContainerDefinition(),
            'customerId' => $entity->getCustomer() ? $entity->getCustomer()->getId() : null
        ];

        $this->dalEntityRepository->upsert(
            [$data],
            $context
        );

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
            new Criteria(
                [$id]
            ),
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
