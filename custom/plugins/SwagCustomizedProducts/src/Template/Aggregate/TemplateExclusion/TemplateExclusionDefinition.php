<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateExclusion;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionCondition\TemplateExclusionConditionDefinition;
use Swag\CustomizedProducts\Template\TemplateDefinition;

class TemplateExclusionDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'swag_customized_products_template_exclusion';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return TemplateExclusionEntity::class;
    }

    public function getCollectionClass(): string
    {
        return TemplateExclusionCollection::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return TemplateDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new VersionField(),
            (new StringField('name', 'name'))->addFlags(new Required()),

            (new OneToManyAssociationField('conditions', TemplateExclusionConditionDefinition::class, 'template_exclusion_id'))->addFlags(new CascadeDelete()),

            (new FkField('template_id', 'templateId', TemplateDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(TemplateDefinition::class, 'template_version_id'))->addFlags(new Required()),
            new ManyToOneAssociationField('template', 'template_id', TemplateDefinition::class),
        ]);
    }
}
