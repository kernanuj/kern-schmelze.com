<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValueTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                             add(TemplateOptionValueTranslationEntity $entity)
 * @method void                                             set(string $key, TemplateOptionValueTranslationEntity $entity)
 * @method \Generator<TemplateOptionValueTranslationEntity> getIterator()
 * @method TemplateOptionValueTranslationEntity[]           getElements()
 * @method TemplateOptionValueTranslationEntity|null        get(string $key)
 * @method TemplateOptionValueTranslationEntity|null        first()
 * @method TemplateOptionValueTranslationEntity|null        last()
 */
class TemplateOptionValueTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TemplateOptionValueTranslationEntity::class;
    }
}
