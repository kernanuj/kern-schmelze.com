<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateExclusion;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                add(TemplateExclusionEntity $entity)
 * @method void                                set(string $key, TemplateExclusionEntity $entity)
 * @method \Generator<TemplateExclusionEntity> getIterator()
 * @method TemplateExclusionEntity[]           getElements()
 * @method TemplateExclusionEntity|null        get(string $key)
 * @method TemplateExclusionEntity|null        first()
 * @method TemplateExclusionEntity|null        last()
 */
class TemplateExclusionCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TemplateExclusionEntity::class;
    }
}
