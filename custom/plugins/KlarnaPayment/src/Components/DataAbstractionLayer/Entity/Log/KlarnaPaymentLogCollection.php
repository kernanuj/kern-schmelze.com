<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\DataAbstractionLayer\Entity\Log;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void             add(KlarnaPaymentLogEntity $entity)
 * @method void             set(string $key, KlarnaPaymentLogEntity $entity)
 * @method KlarnaPaymentLogEntity[]    getIterator()
 * @method KlarnaPaymentLogEntity[]    getElements()
 * @method null|KlarnaPaymentLogEntity get(string $key)
 * @method null|KlarnaPaymentLogEntity first()
 * @method null|KlarnaPaymentLogEntity last()
 */
class KlarnaPaymentLogCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return KlarnaPaymentLogEntity::class;
    }
}
