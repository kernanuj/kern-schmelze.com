<?php declare(strict_types=1);

namespace InvExportLabel\Service\TypeInstance\SimpleProduct;

use InvExportLabel\Service\RendererInterface;
use InvExportLabel\Service\TypeInstanceInterface;
use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\SourceItemInterface;
use InvMixerProduct\Helper\OrderLineItemEntityAccessor;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderCollection;
use function assert;

/**
 * Class TypeInstance
 * @package InvExportLabel\Service\TypeInstance\SimpleProduct
 */
class TypeInstance implements TypeInstanceInterface
{
    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var SourceItemConverter
     */
    private $sourceItemConverter;

    /**
     * TypeInstance constructor.
     * @param Renderer $renderer
     * @param SourceItemConverter $sourceItemConverter
     */
    public function __construct(Renderer $renderer, SourceItemConverter $sourceItemConverter)
    {
        $this->renderer = $renderer;
        $this->sourceItemConverter = $sourceItemConverter;
    }


    /**
     * @inheritDoc
     *
     * @todo method is incomplete
     */
    public function extractOrderLineItems(OrderCollection $orderCollection): OrderLineItemCollection
    {
        $filteredCollection = new OrderLineItemCollection();

        foreach ($orderCollection as $orderEntity) {
            foreach ($orderEntity->getLineItems() as $lineItemEntity) {
                if(!$lineItemEntity->getProduct()){
                    continue;
                }
                $lineItemEntity->setOrder($orderEntity);
                $filteredCollection->add($lineItemEntity);
            }
        }

        return $filteredCollection;
    }

    /**
     * @inheritDoc
     */
    public function convertOrderLineItemToSourceItem(
        OrderLineItemEntity $orderLineItemEntity,
        ExportRequestConfiguration $exportRequestConfiguration
    ): SourceItemInterface {
        assert($this->isLineItemWithSubject($orderLineItemEntity));

        return $this->sourceItemConverter->convertOrderLineItem(
            $orderLineItemEntity,
            $exportRequestConfiguration
        );
    }

    /**
     * @param OrderLineItemEntity $orderLineItemEntity
     * @return bool
     */
    private function isLineItemWithSubject(OrderLineItemEntity $orderLineItemEntity): bool
    {

        if (true === OrderLineItemEntityAccessor::isContainsMixContainerProduct($orderLineItemEntity)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getRenderer(): RendererInterface
    {
        return $this->renderer;
    }
}