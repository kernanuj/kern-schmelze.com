<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Extension;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Swag\CustomizedProducts\Template\TemplateDefinition;

class ProductExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToOneAssociationField(
                Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN,
                Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_FK_COLUMN,
                TemplateDefinition::class,
                'id'
            ))->addFlags(new Inherited())
        );
        $collection->add(
            (new FkField(
                Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_FK_COLUMN,
                'swagCustomizedProductsTemplateId',
                TemplateDefinition::class
            ))->addFlags(new Inherited())
        );
        $collection->add(
            (new ReferenceVersionField(
                TemplateDefinition::class,
                Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_REFERENCE_VERSION_COLUMN
            ))->addFlags(new Inherited())
        );
    }

    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}
