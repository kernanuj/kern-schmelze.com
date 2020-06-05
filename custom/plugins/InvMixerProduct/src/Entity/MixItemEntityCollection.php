<?php declare(strict_types=1);

namespace InvMixerProduct\Entity;

use InvMixerProduct\Entity\MixItemEntity as SubjectEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(SubjectEntity $entity)
 * @method void              set(string $key, SubjectEntity $entity)
 * @method SubjectEntity[]|\Traversable    getIterator()
 * @method SubjectEntity[]|\Traversable    getElements()
 * @method SubjectEntity|null get(string $key)
 * @method SubjectEntity|null first()
 * @method SubjectEntity|null last()
 */
class MixItemEntityCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return SubjectEntity::class;
    }
}
