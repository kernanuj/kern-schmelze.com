<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPrice;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                  add(TemplateOptionPriceEntity $entity)
 * @method void                                  set(string $key, TemplateOptionPriceEntity $entity)
 * @method \Generator<TemplateOptionPriceEntity> getIterator()
 * @method TemplateOptionPriceEntity[]           getElements()
 * @method TemplateOptionPriceEntity|null        get(string $key)
 * @method TemplateOptionPriceEntity|null        first()
 * @method TemplateOptionPriceEntity|null        last()
 */
class TemplateOptionPriceCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TemplateOptionPriceEntity::class;
    }
}
