<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Extension;

use Shopware\Core\Content\Cms\Aggregate\CmsSection\CmsSectionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtensionInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\CmsExtensions\ScrollNavigation\ScrollNavigationDefinition;

class CmsSectionEntityExtension implements EntityExtensionInterface
{
    public const SCROLL_NAVIGATION_ASSOCIATION_PROPERTY_NAME = 'swagCmsExtensionsScrollNavigation';

    /**
     * {@inheritdoc}
     */
    public function getDefinitionClass(): string
    {
        return CmsSectionDefinition::class;
    }

    /**
     * {@inheritdoc}
     */
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToOneAssociationField(
                self::SCROLL_NAVIGATION_ASSOCIATION_PROPERTY_NAME,
                'id',
                'id',
                ScrollNavigationDefinition::class,
                false
            )
        );
    }
}
