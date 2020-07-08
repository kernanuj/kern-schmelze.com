<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Content\Product\SalesChannel;

use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Storefront\Page\Product\ProductLoaderCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelProductSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProductLoaderCriteriaEvent::class => 'addCustomizedProductsDetailAssociations',
            ProductListingCriteriaEvent::class => 'addCustomizedProductsListingAssociation',
        ];
    }

    public function addCustomizedProductsDetailAssociations(ProductLoaderCriteriaEvent $event): void
    {
        $criteria = $event->getCriteria();

        $criteria->addAssociation('swagCustomizedProductsTemplate.options.values.prices');
        $criteria->addAssociation('swagCustomizedProductsTemplate.options.prices');
        $criteria->addAssociation('swagCustomizedProductsTemplate.media');
        $criteria->getAssociation('swagCustomizedProductsTemplate.options')
            ->addSorting(new FieldSorting('position', FieldSorting::ASCENDING));
        $criteria->getAssociation('swagCustomizedProductsTemplate.options.values')
            ->addSorting(new FieldSorting('position', FieldSorting::ASCENDING));
    }

    public function addCustomizedProductsListingAssociation(ProductListingCriteriaEvent $event): void
    {
        $criteria = $event->getCriteria();

        $criteria->addAssociation('swagCustomizedProductsTemplate');
    }
}
