<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\SourceItemInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderCollection;

/**
 * Interface TypeInstanceInterface
 * @package InvExportLabel\Service
 */
interface TypeInstanceInterface
{

    /**
     * Extracts all line items that match a the current type and should be used for further processing.
     *
     * @param OrderCollection $orderCollection
     * @return OrderLineItemCollection
     */
    public function extractOrderLineItems(OrderCollection $orderCollection): OrderLineItemCollection;

    /**
     * @return RendererInterface
     */
    public function getRenderer(): RendererInterface;

    /**
     * @param OrderLineItemEntity $orderLineItemEntity
     * @param ExportRequestConfiguration $exportRequestConfiguration
     * @return SourceItemInterface
     */
    public function convertOrderLineItemToSourceItem(
        OrderLineItemEntity $orderLineItemEntity,
        ExportRequestConfiguration $exportRequestConfiguration
    ): SourceItemInterface;


}
