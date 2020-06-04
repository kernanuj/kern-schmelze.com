<?php declare(strict_types=1);

namespace InvMixerProduct\Repository;


use InvMixerProduct\Entity\MixEntity as SubjectEntity;
use InvMixerProduct\Entity\MixItemEntity;
use InvMixerProduct\Exception\EntityNotFoundException;
use InvMixerProduct\Struct\ContainerDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
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
     * @var EntityRepositoryInterface
     */
    private $mixItemRepository;

    /**
     * MixEntityRepository constructor.
     * @param EntityRepositoryInterface $dalEntityRepository
     * @param EntityRepositoryInterface $mixItemRepository
     */
    public function __construct(
        EntityRepositoryInterface $dalEntityRepository,
        EntityRepositoryInterface $mixItemRepository
    ) {
        $this->dalEntityRepository = $dalEntityRepository;
        $this->mixItemRepository = $mixItemRepository;
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
     * @throws \Exception
     */
    public function save(SubjectEntity $entity, Context $context)
    {
        // Items have to removed manually since shopware does not tracked unlinked associated entities
        $this->removeItemsNotExistentInSubject(
            $entity,
            $context
        );

        $data = [
            'id' => $entity->getId(),
            'created_at' => $entity->getCreatedAt(),
            'updated_at' => $entity->getUpdatedAt(),
            'containerDefinition' => $entity->getContainerDefinition(),
            'customerId' => $entity->getCustomer() ? $entity->getCustomer()->getId() : null,
            'items' => !is_null($entity->getItems()) ?
                array_map(
                    function (MixItemEntity $item) {
                        return [
                            'id' => $item->getId(),
                            'productId' => $item->getProduct()->getId(),
                            'mixId' => $item->getMixId(),
                            'quantity' => $item->getQuantity()
                        ];
                    },
                    iterator_to_array($entity->getItems()->getIterator()))
                : null
        ];

        $this->dalEntityRepository->upsert(
            [$data],
            $context
        );

    }

    /**
     * @param SubjectEntity $subject
     * @param Context $context
     * @return $this
     */
    private function removeItemsNotExistentInSubject(SubjectEntity $subject, Context $context): self
    {
        if(!$subject->getItems()){
            return $this;
        }

        $idsAfter = $subject->getItems()->getIds();
        $idsCurrent = $this->mixItemRepository->searchIds(
            (new Criteria())->addFilter(new EqualsFilter('mixId', $subject->getId())),
            $context
        )->getIds();
        $idsToRemove = array_filter($idsCurrent, function ($idSet) use ($idsAfter) {
            if (in_array($idSet['id'], $idsAfter)) {
                return false;
            }
            return true;
        });

        if(empty($idsToRemove)){
            return $this;
        }

        //seriously?
        $idsToRemove = array_map(
            function ($idSet) {
                return [
                    'id' => $idSet['id'],
                    'mixId' => $idSet['mix_id'],
                    'productId' => $idSet['product_id'],
                ];
            },
            $idsToRemove
        );
        sort($idsToRemove);
        $this->mixItemRepository->delete(
            $idsToRemove,
            $context
        );

        return $this;
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
            ))
                ->addAssociation('items')
                ->addAssociation('items.mix')
                ->addAssociation('items.product'),
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
