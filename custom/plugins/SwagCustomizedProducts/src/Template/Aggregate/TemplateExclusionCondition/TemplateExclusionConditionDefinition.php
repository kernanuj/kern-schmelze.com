<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionCondition;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusion\TemplateExclusionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionConditionValues\TemplateExclusionConditionValuesDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperator\TemplateExclusionOperatorDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\TemplateOptionValueDefinition;

class TemplateExclusionConditionDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'swag_customized_products_template_exclusion_condition';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return TemplateExclusionConditionEntity::class;
    }

    public function getCollectionClass(): string
    {
        return TemplateExclusionConditionCollection::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return TemplateExclusionDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new VersionField(),

            (new FkField('template_exclusion_id', 'templateExclusionId', TemplateExclusionDefinition::class))->addFlags(new Required()),
            new ManyToOneAssociationField('templateExclusion', 'template_exclusion_id', TemplateExclusionDefinition::class),
            (new ReferenceVersionField(TemplateExclusionDefinition::class, 'template_exclusion_version_id'))->addFlags(new Required()),

            (new FkField('template_option_id', 'templateOptionId', TemplateOptionDefinition::class))->addFlags(new Required()),
            new ManyToOneAssociationField('templateOption', 'template_option_id', TemplateOptionDefinition::class),
            (new ReferenceVersionField(TemplateOptionDefinition::class, 'template_option_version_id'))->addFlags(new Required()),

            (new FkField('template_exclusion_operator_id', 'templateExclusionOperatorId', TemplateExclusionOperatorDefinition::class))->addFlags(new Required()),
            new ManyToOneAssociationField('templateExclusionOperator', 'template_exclusion_operator_id', TemplateExclusionOperatorDefinition::class),

            (new ManyToManyAssociationField(
                'templateOptionValues',
                TemplateOptionValueDefinition::class,
                TemplateExclusionConditionValuesDefinition::class,
                'template_exclusion_condition_id',
                'template_option_value_id'
            ))->addFlags(new CascadeDelete()),
        ]);
    }
}
