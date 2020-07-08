<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Extension;

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperatorTranslation\TemplateExclusionOperatorTranslationDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionTranslation\TemplateOptionTranslationDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValueTranslation\TemplateOptionValueTranslationDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateTranslation\TemplateTranslationDefinition;

class LanguageExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'customizedProductsTemplateTranslations',
                TemplateTranslationDefinition::class,
                'language_id'
            )
        );

        $collection->add(
            new OneToManyAssociationField(
                'customizedProductsTemplateOptionTranslations',
                TemplateOptionTranslationDefinition::class,
                'language_id'
            )
        );

        $collection->add(
            new OneToManyAssociationField(
                'customizedProductsTemplateOptionValueTranslations',
                TemplateOptionValueTranslationDefinition::class,
                'language_id'
            )
        );

        $collection->add(
            new OneToManyAssociationField(
                'customizedProductsTemplateExclusionOperatorTranslations',
                TemplateExclusionOperatorTranslationDefinition::class,
                'language_id'
            )
        );
    }

    public function getDefinitionClass(): string
    {
        return LanguageDefinition::class;
    }
}
