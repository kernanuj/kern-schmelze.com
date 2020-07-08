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
use Shopware\Core\System\Tax\TaxDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\TemplateOptionValueDefinition;

class TaxExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'customizedProductsOptions',
                TemplateOptionDefinition::class,
                'tax_id'
            )
        );

        $collection->add(
            new OneToManyAssociationField(
                'customizedProductsOptionValues',
                TemplateOptionValueDefinition::class,
                'tax_id'
            )
        );
    }

    public function getDefinitionClass(): string
    {
        return TaxDefinition::class;
    }
}
