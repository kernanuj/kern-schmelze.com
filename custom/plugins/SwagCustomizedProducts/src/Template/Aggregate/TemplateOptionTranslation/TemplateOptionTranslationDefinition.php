<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionDefinition;

class TemplateOptionTranslationDefinition extends EntityTranslationDefinition
{
    public const ENTITY_NAME = 'swag_customized_products_template_option_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return TemplateOptionTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return TemplateOptionTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return TemplateOptionDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('display_name', 'displayName'))->setFlags(new Required()),
            (new LongTextField('description', 'description'))->addFlags(new AllowHtml()),
        ]);
    }
}
