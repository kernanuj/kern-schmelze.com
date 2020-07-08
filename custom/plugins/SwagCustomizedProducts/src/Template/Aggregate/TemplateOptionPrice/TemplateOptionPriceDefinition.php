<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPrice;

use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PriceField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionDefinition;

class TemplateOptionPriceDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'swag_customized_products_template_option_price';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return TemplateOptionPriceEntity::class;
    }

    public function getCollectionClass(): string
    {
        return TemplateOptionPriceCollection::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return TemplateOptionDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            new VersionField(),
            (new FkField('template_option_id', 'templateOptionId', TemplateOptionDefinition::class))->setFlags(new Required()),
            (new ReferenceVersionField(TemplateOptionDefinition::class, 'template_option_version_id'))->addFlags(new Required()),

            new PriceField('price', 'price'),
            new FloatField('percentage_surcharge', 'percentageSurcharge'),

            new ManyToOneAssociationField('templateOption', 'template_option_id', TemplateOptionDefinition::class),
            new FkField('rule_id', 'ruleId', RuleDefinition::class),
            new ManyToOneAssociationField('rule', 'rule_id', RuleDefinition::class, 'id'),
        ]);
    }
}
