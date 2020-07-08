<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\SalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelDefinitionInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionDefinition;

class SalesChannelTemplateOptionDefinition extends TemplateOptionDefinition implements SalesChannelDefinitionInterface
{
    public function processCriteria(Criteria $criteria, SalesChannelContext $context): void
    {
        $criteria->addAssociation('values')
            ->addAssociation('prices')
            ->addAssociation('values.prices');
    }
}
