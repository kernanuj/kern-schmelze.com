<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                  add(TemplateOptionValueEntity $entity)
 * @method void                                  set(string $key, TemplateOptionValueEntity $entity)
 * @method \Generator<TemplateOptionValueEntity> getIterator()
 * @method TemplateOptionValueEntity[]           getElements()
 * @method TemplateOptionValueEntity|null        get(string $key)
 * @method TemplateOptionValueEntity|null        first()
 * @method TemplateOptionValueEntity|null        last()
 */
class TemplateOptionValueCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TemplateOptionValueEntity::class;
    }
}
