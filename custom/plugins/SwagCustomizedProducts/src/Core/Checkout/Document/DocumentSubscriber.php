<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Checkout\Document;

use Shopware\Core\Checkout\Document\Event\DocumentOrderCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DocumentSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            DocumentOrderCriteriaEvent::class => 'addCustomizedProducts',
        ];
    }

    public function addCustomizedProducts(DocumentOrderCriteriaEvent $event): void
    {
        $criteria = $event->getCriteria();
        $criteria->addAssociation('lineItems.product.swagCustomizedProductsTemplate.options.values.prices');
        $criteria->addAssociation('lineItems.product.swagCustomizedProductsTemplate.options.prices');
    }
}
