<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionConditionValues;

use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionCondition\TemplateExclusionConditionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\TemplateOptionValueDefinition;

class TemplateExclusionConditionValuesDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'swag_customized_products_template_exclusion_condition_values';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('template_exclusion_condition_id', 'templateExclusionConditionId', TemplateExclusionConditionDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('templateExclusionCondition', 'template_exclusion_condition_id', TemplateExclusionConditionDefinition::class),
            (new ReferenceVersionField(TemplateExclusionConditionDefinition::class, 'swag_customized_products_template_exclusion_condition_version_id'))->addFlags(new Required()),

            (new FkField('template_option_value_id', 'templateOptionValueId', TemplateOptionValueDefinition::class))->addFlags(new Required()),
            new ManyToOneAssociationField('templateOptionValue', 'template_option_value_id', TemplateOptionValueDefinition::class),
            (new ReferenceVersionField(TemplateOptionValueDefinition::class, 'swag_customized_products_template_option_value_version_id'))->addFlags(new Required()),

            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}
