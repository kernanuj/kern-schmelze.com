<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Extension;

use Shopware\Core\Framework\DataAbstractionLayer\EntityExtensionInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;
use Swag\CmsExtensions\ScrollNavigation\Aggregate\ScrollNavigationTranslation\ScrollNavigationTranslationDefinition;

class LanguageExtension implements EntityExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinitionClass(): string
    {
        return LanguageDefinition::class;
    }

    /**
     * {@inheritdoc}
     */
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'cmsExtensionsScrollNavigationTranslations',
                ScrollNavigationTranslationDefinition::class,
                'language_id'
            )
        );
    }
}
