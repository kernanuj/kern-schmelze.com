<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperatorTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                                   add(TemplateExclusionOperatorTranslationEntity $entity)
 * @method void                                                   set(string $key, TemplateExclusionOperatorTranslationEntity $entity)
 * @method \Generator<TemplateExclusionOperatorTranslationEntity> getIterator()
 * @method TemplateExclusionOperatorTranslationEntity[]           getElements()
 * @method TemplateExclusionOperatorTranslationEntity|null        get(string $key)
 * @method TemplateExclusionOperatorTranslationEntity|null        first()
 * @method TemplateExclusionOperatorTranslationEntity|null        last()
 */
class TemplateExclusionOperatorTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TemplateExclusionOperatorTranslationEntity::class;
    }
}
