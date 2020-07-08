<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperator;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                        add(TemplateExclusionOperatorEntity $entity)
 * @method void                                        set(string $key, TemplateExclusionOperatorEntity $entity)
 * @method \Generator<TemplateExclusionOperatorEntity> getIterator()
 * @method TemplateExclusionOperatorEntity[]           getElements()
 * @method TemplateExclusionOperatorEntity|null        get(string $key)
 * @method TemplateExclusionOperatorEntity|null        first()
 * @method TemplateExclusionOperatorEntity|null        last()
 */
class TemplateExclusionOperatorCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TemplateExclusionOperatorEntity::class;
    }
}
