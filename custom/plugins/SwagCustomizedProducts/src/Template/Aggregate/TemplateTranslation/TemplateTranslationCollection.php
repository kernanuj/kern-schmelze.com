<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                  add(TemplateTranslationEntity $entity)
 * @method void                                  set(string $key, TemplateTranslationEntity $entity)
 * @method \Generator<TemplateTranslationEntity> getIterator()
 * @method TemplateTranslationEntity[]           getElements()
 * @method TemplateTranslationEntity|null        get(string $key)
 * @method TemplateTranslationEntity|null        first()
 * @method TemplateTranslationEntity|null        last()
 */
class TemplateTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TemplateTranslationEntity::class;
    }
}
