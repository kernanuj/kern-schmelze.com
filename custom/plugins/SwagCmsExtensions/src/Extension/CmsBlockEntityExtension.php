<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Extension;

use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\CmsExtensions\BlockRule\BlockRuleDefinition;
use Swag\CmsExtensions\Quickview\QuickviewDefinition;

class CmsBlockEntityExtension extends EntityExtension
{
    public const QUICKVIEW_ASSOCIATION_PROPERTY_NAME = 'swagCmsExtensionsQuickview';
    public const BLOCK_RULE_ASSOCIATION_PROPERTY_NAME = 'swagCmsExtensionsBlockRule';

    public function getDefinitionClass(): string
    {
        return CmsBlockDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToOneAssociationField(
                self::QUICKVIEW_ASSOCIATION_PROPERTY_NAME,
                'id',
                QuickviewDefinition::CMS_BLOCK_FOREIGN_KEY_STORAGE_NAME,
                QuickviewDefinition::class,
                false
            ))->addFlags(new CascadeDelete())
        );

        $collection->add(
            (new OneToOneAssociationField(
                self::BLOCK_RULE_ASSOCIATION_PROPERTY_NAME,
                'id',
                BlockRuleDefinition::CMS_BLOCK_FOREIGN_KEY_STORAGE_NAME,
                BlockRuleDefinition::class,
                false
            ))->addFlags(new CascadeDelete())
        );
    }
}
