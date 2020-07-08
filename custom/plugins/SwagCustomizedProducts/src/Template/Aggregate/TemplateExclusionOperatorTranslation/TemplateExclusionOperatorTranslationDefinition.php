<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperatorTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperator\TemplateExclusionOperatorDefinition;

class TemplateExclusionOperatorTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'swag_customized_products_template_exclusion_operator_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return TemplateExclusionOperatorTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return TemplateExclusionOperatorTranslationEntity::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return TemplateExclusionOperatorDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('label', 'label'))->addFlags(new Required()),
        ]);
    }
}
