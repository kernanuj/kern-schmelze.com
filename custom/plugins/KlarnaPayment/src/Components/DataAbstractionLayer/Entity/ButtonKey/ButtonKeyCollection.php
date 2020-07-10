<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\DataAbstractionLayer\Entity\ButtonKey;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void             add(ButtonKeyEntity $entity)
 * @method void             set(string $key, ButtonKeyEntity $entity)
 * @method ButtonKeyEntity[]    getIterator()
 * @method ButtonKeyEntity[]    getElements()
 * @method null|ButtonKeyEntity get(string $key)
 * @method null|ButtonKeyEntity first()
 * @method null|ButtonKeyEntity last()
 */
class ButtonKeyCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ButtonKeyEntity::class;
    }
}
