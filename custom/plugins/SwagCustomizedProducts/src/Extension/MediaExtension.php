<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Extension;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ReadProtected;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Swag\CustomizedProducts\Template\TemplateDefinition;

class MediaExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN, TemplateDefinition::class, 'media_id'))->addFlags(new ReadProtected(SalesChannelApiSource::class))
        );
    }

    public function getDefinitionClass(): string
    {
        return MediaDefinition::class;
    }
}
