<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                             add(TemplateOptionEntity $entity)
 * @method void                             set(string $key, TemplateOptionEntity $entity)
 * @method \Generator<TemplateOptionEntity> getIterator()
 * @method TemplateOptionEntity[]           getElements()
 * @method TemplateOptionEntity|null        get(string $key)
 * @method TemplateOptionEntity|null        first()
 * @method TemplateOptionEntity|null        last()
 */
class TemplateOptionCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TemplateOptionEntity::class;
    }
}
