<?php declare(strict_types=1);

namespace InvMixerProduct\Repository;


use Doctrine\DBAL\Connection;
use InvMixerProduct\Constants;
use InvMixerProduct\Entity\MixEntity as SubjectEntity;
use InvMixerProduct\Entity\MixItemEntity;
use InvMixerProduct\Exception\EntityNotFoundException;
use InvMixerProduct\Service\ConfigurationInterface;
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
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * MixEntityRepository constructor.
     * @param EntityRepositoryInterface $dalEntityRepository
     * @param EntityRepositoryInterface $mixItemRepository
     * @param ConfigurationInterface $configuration
     * @param Connection $connection
     */
    public function __construct(
        EntityRepositoryInterface $dalEntityRepository,
        EntityRepositoryInterface $mixItemRepository,
        ConfigurationInterface $configuration,
        Connection $connection
    ) {
        $this->dalEntityRepository = $dalEntityRepository;
        $this->mixItemRepository = $mixItemRepository;
        $this->configuration = $configuration;
        $this->connection = $connection;
    }


    /**
     * @return SubjectEntity
     */
    public function create(): SubjectEntity
    {
        $entity = new SubjectEntity();
        $entity->setContainerDefinition(
            $this->configuration->getDefaultContainerDefinition()
        );
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

        if (true !== $entity->hasDisplayId()) {
            $entity->setDisplayId($this->getNextMixDisplayId());
        }

        $data = [
            'id' => $entity->getId(),
            'created_at' => $entity->getCreatedAt(),
            'updated_at' => $entity->getUpdatedAt(),
            'displayId' => $entity->getDisplayId(),
            'containerDefinition' => $entity->getContainerDefinition(),
            'customerId' => $entity->getCustomer() ? $entity->getCustomer()->getId() : null,
            'label' => $entity->getLabel(),
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
        if (!$subject->getItems()) {
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

        if (empty($idsToRemove)) {
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
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getNextMixDisplayId(): int
    {
        $nextID = $this->connection->fetchColumn(
            'SELECT max(display_id) FROM inv_mixer_product__mix',
            [],
            0
        );

        if($nextID < Constants::MIX_DISPLAY_ID_OFFSET){
            $nextID = Constants::MIX_DISPLAY_ID_OFFSET;
        }
        $nextID = (int)$nextID;

        return $nextID + 1;
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
                ->addAssociation('items.product')
                ->addAssociation('items.product.prices')
                ->addAssociation('items.product.unit')
                ->addAssociation('items.product.cover'),
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
