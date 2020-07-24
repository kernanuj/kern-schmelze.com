<?php declare(strict_types=1);

namespace InvMixerProduct\EventSubscriber;

use InvMixerProduct\Helper\OrderLineItemEntityAccessor;
use Shopware\Core\Checkout\Document\Event\DocumentTemplateRendererParameterEvent;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class DocumentTemplateRendererParameterEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents(): array
    {
        return [
            DocumentTemplateRendererParameterEvent::class => 'onDocumentTemplateRendererParameterEvent'
        ];
    }

    /**
     * Handle document generation grouping parent/child line items
     *
     * @param DocumentTemplateRendererParameterEvent $event
     */
    public function onDocumentTemplateRendererParameterEvent(DocumentTemplateRendererParameterEvent $event): void
    {
        $parameters = $event->getParameters();

        $originalOrder = array_key_exists('order', $parameters) ? $parameters['order'] : null;

        if (is_null($originalOrder)) {
            return;
        }
        $root = new OrderLineItemCollection();
        $this->attachNonAffectedLineItems($originalOrder->getLineItems(), $root);

        $this->attachAffectedLineItems($originalOrder->getLineItems(), $root);

        $originalOrder->setLineItems($root);

    }

    /**
     * @param OrderLineItemCollection $originalOrderLineItemCollection
     * @param OrderLineItemCollection $root
     */
    private function attachNonAffectedLineItems(
        OrderLineItemCollection $originalOrderLineItemCollection,
        OrderLineItemCollection $root
    ): void {
        foreach ($originalOrderLineItemCollection->getElements() as $originalOrderLineItem) {
            if (
                false === OrderLineItemEntityAccessor::isContainsMixContainerProduct($originalOrderLineItem)
                && false === OrderLineItemEntityAccessor::isContainsMixBaseProduct($originalOrderLineItem)
                && false === OrderLineItemEntityAccessor::isContainsMixChildProduct($originalOrderLineItem)
            ) {
                $root->add($originalOrderLineItem);
            }
        }
    }

    /**
     * @param OrderLineItemCollection $originalOrderLineItemCollection
     * @param OrderLineItemCollection $root
     */
    private function attachAffectedLineItems(
        OrderLineItemCollection $originalOrderLineItemCollection,
        OrderLineItemCollection $root
    ): void {

        $index = $root->count() - 1;
        foreach ($originalOrderLineItemCollection->getElements() as $originalOrderLineItem) {
            if (false === OrderLineItemEntityAccessor::isContainsMixContainerProduct($originalOrderLineItem)) {
                continue;
            }

            $baseProduct = $this->findBaseProductForContainerProductLineItem(
                $originalOrderLineItemCollection,
                $originalOrderLineItem,
                $index
            );

            $childProducts = $this->findChildProductsForContainerProductLineItem(
                $originalOrderLineItemCollection,
                $originalOrderLineItem,
                $index
            );

            $root->add($originalOrderLineItem);
            $root->add($baseProduct);
            foreach ($childProducts as $childProductLineItem) {
                $root->add($childProductLineItem);
            }

            $index++;
        }
    }

    /**
     * @param OrderLineItemCollection $originalOrderLineItemCollection
     * @param OrderLineItemEntity $originalOrderContainerProductLineItem
     * @param int $indexOffset
     * @return OrderLineItemEntity
     */
    private function findBaseProductForContainerProductLineItem(
        OrderLineItemCollection $originalOrderLineItemCollection,
        OrderLineItemEntity $originalOrderContainerProductLineItem,
        int $indexOffset

    ): OrderLineItemEntity {
        foreach ($originalOrderLineItemCollection->getElements() as $originalOrderLineItem) {
            if (true !== OrderLineItemEntityAccessor::isContainsMixBaseProduct($originalOrderLineItem)) {
                continue;
            }

            if ($originalOrderLineItem->getParentId() !== $originalOrderContainerProductLineItem->getId()) {
                continue;
            }

            $originalOrderLineItem->setPayload(
                array_merge(
                    $originalOrderLineItem->getPayload(),
                    [
                        'inv_is_child' => true,
                        'inv_position_as_child' => $indexOffset .'.1'
                    ]
                )
            );
            return $originalOrderLineItem;

        }
    }

    /**
     * @param OrderLineItemCollection $originalOrderLineItemCollection
     * @param OrderLineItemEntity $originalOrderContainerProductLineItem
     * @param int $indexOffset
     * @return array
     */
    private function findChildProductsForContainerProductLineItem(
        OrderLineItemCollection $originalOrderLineItemCollection,
        OrderLineItemEntity $originalOrderContainerProductLineItem,
        int $indexOffset
    ): array {

        $items = [];
        $index = 0;
        foreach ($originalOrderLineItemCollection->getElements() as $originalOrderLineItem) {
            if (true !== OrderLineItemEntityAccessor::isContainsMixChildProduct($originalOrderLineItem)) {
                continue;
            }

            if ($originalOrderLineItem->getParentId() !== $originalOrderContainerProductLineItem->getId()) {
                continue;
            }

            $originalOrderLineItem->setPayload(
                array_merge(
                    $originalOrderLineItem->getPayload(),
                    [
                        'inv_is_child' => true,
                        'inv_position_as_child' => sprintf('%d.%d',$indexOffset, ($index + 2))
                    ]
                )
            );
            $items[] = $originalOrderLineItem;
            $index++;

        }
        return $items;
    }

}
