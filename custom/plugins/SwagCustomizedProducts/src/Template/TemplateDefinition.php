<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ReverseInherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusion\TemplateExclusionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateTranslation\TemplateTranslationDefinition;

class TemplateDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'swag_customized_products_template';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return TemplateCollection::class;
    }

    public function getEntityClass(): string
    {
        return TemplateEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'active' => false,
            'stepByStep' => false,
            'confirmInput' => false,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new VersionField(),
            (new ReferenceVersionField(self::class, 'parent_version_id'))->addFlags(new Required()),

            (new StringField('internal_name', 'internalName'))->addFlags(new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),

            (new TranslatedField('displayName'))->addFlags(new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            new TranslatedField('description'),
            new BoolField('active', 'active'),
            new BoolField('step_by_step', 'stepByStep'),
            new BoolField('confirm_input', 'confirmInput'),
            (new JsonField('decision_tree', 'decisionTree'))->addFlags(new WriteProtected(Context::SYSTEM_SCOPE)),

            (new TranslationsAssociationField(TemplateTranslationDefinition::class, 'swag_customized_products_template_id'))->addFlags(new Required()),

            new FkField('media_id', 'mediaId', MediaDefinition::class),
            new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class),

            (new OneToManyAssociationField('options', TemplateOptionDefinition::class, 'template_id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('products', ProductDefinition::class, 'swag_customized_products_template_id'))->addFlags(new ReverseInherited(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN)),
            (new OneToManyAssociationField('exclusions', TemplateExclusionDefinition::class, 'template_id'))->addFlags(new CascadeDelete()),
        ]);
    }
}
