<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                        add(TemplateOptionTranslationEntity $entity)
 * @method void                                        set(string $key, TemplateOptionTranslationEntity $entity)
 * @method \Generator<TemplateOptionTranslationEntity> getIterator()
 * @method TemplateOptionTranslationEntity[]           getElements()
 * @method TemplateOptionTranslationEntity|null        get(string $key)
 * @method TemplateOptionTranslationEntity|null        first()
 * @method TemplateOptionTranslationEntity|null        last()
 */
class TemplateOptionTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TemplateOptionTranslationEntity::class;
    }
}
