<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PriceField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Tax\TaxDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionCondition\TemplateExclusionConditionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionConditionValues\TemplateExclusionConditionValuesDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValuePrice\TemplateOptionValuePriceDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValueTranslation\TemplateOptionValueTranslationDefinition;

class TemplateOptionValueDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'swag_customized_products_template_option_value';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return TemplateOptionValueEntity::class;
    }

    public function getCollectionClass(): string
    {
        return TemplateOptionValueCollection::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return TemplateOptionDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new VersionField(),
            (new FkField('template_option_id', 'templateOptionId', TemplateOptionDefinition::class))->setFlags(new Required()),
            (new ReferenceVersionField(TemplateOptionDefinition::class, 'template_option_version_id'))->addFlags(new Required()),

            new JsonField('value', 'value'),
            (new TranslatedField('displayName'))->addFlags(new SearchRanking(SearchRanking::LOW_SEARCH_RAKING)),
            new StringField('item_number', 'itemNumber'),
            new BoolField('default', 'default'),
            new BoolField('one_time_surcharge', 'oneTimeSurcharge'),
            new BoolField('relative_surcharge', 'relativeSurcharge'),
            new BoolField('advanced_surcharge', 'advancedSurcharge'),
            (new IntField('position', 'position'))->addFlags(new Required()),
            new PriceField('price', 'price'),
            new FloatField('percentage_surcharge', 'percentageSurcharge'),

            (new TranslationsAssociationField(TemplateOptionValueTranslationDefinition::class, 'swag_customized_products_template_option_value_id'))->addFlags(new Required()),
            (new OneToManyAssociationField('prices', TemplateOptionValuePriceDefinition::class, 'template_option_value_id'))->addFlags(new CascadeDelete()),
            new ManyToOneAssociationField('templateOption', 'template_option_id', TemplateOptionDefinition::class),
            new FkField('tax_id', 'taxId', TaxDefinition::class),
            new ManyToOneAssociationField('tax', 'tax_id', TaxDefinition::class),
            new ManyToManyAssociationField('templateExclusionConditions', TemplateExclusionConditionDefinition::class, TemplateExclusionConditionValuesDefinition::class, 'template_option_value_id', 'template_exclusion_condition_id'),
        ]);
    }
}
