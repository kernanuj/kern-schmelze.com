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
use Shopware\Core\System\Currency\CurrencyDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPrice\TemplateOptionPriceDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValuePrice\TemplateOptionValuePriceDefinition;

class CurrencyExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'customizedProductsTemplateOptionPrices',
                TemplateOptionPriceDefinition::class,
                'currency_id'
            )
        );

        $collection->add(
            new OneToManyAssociationField(
                'customizedProductsTemplateOptionValuePrices',
                TemplateOptionValuePriceDefinition::class,
                'currency_id'
            )
        );
    }

    public function getDefinitionClass(): string
    {
        return CurrencyDefinition::class;
    }
}
