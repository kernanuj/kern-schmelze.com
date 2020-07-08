<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Extension;

use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPrice\TemplateOptionPriceDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValuePrice\TemplateOptionValuePriceDefinition;

class RuleExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'customizedProductsTemplateOptionPrices',
                TemplateOptionPriceDefinition::class,
                'rule_id'
            )
        );

        $collection->add(
            new OneToManyAssociationField(
                'customizedProductsTemplateOptionValuePrices',
                TemplateOptionValuePriceDefinition::class,
                'rule_id'
            )
        );
    }

    public function getDefinitionClass(): string
    {
        return RuleDefinition::class;
    }
}
