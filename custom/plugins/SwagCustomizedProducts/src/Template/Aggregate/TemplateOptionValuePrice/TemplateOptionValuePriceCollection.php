<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValuePrice;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                       add(TemplateOptionValuePriceEntity $entity)
 * @method void                                       set(string $key, TemplateOptionValuePriceEntity $entity)
 * @method \Generator<TemplateOptionValuePriceEntity> getIterator()
 * @method TemplateOptionValuePriceEntity[]           getElements()
 * @method TemplateOptionValuePriceEntity|null        get(string $key)
 * @method TemplateOptionValuePriceEntity|null        first()
 * @method TemplateOptionValuePriceEntity|null        last()
 */
class TemplateOptionValuePriceCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TemplateOptionValuePriceEntity::class;
    }
}
