<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionCondition;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                         add(TemplateExclusionConditionEntity $entity)
 * @method void                                         set(string $key, TemplateExclusionConditionEntity $entity)
 * @method \Generator<TemplateExclusionConditionEntity> getIterator()
 * @method TemplateExclusionConditionEntity[]           getElements()
 * @method TemplateExclusionConditionEntity|null        get(string $key)
 * @method TemplateExclusionConditionEntity|null        first()
 * @method TemplateExclusionConditionEntity|null        last()
 */
class TemplateExclusionConditionCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TemplateExclusionConditionEntity::class;
    }
}
