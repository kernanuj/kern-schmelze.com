<?php declare(strict_types=1);

namespace InvExportLabel\Service\TypeInstance\MixerProduct;

use InvExportLabel\Service\RendererInterface;
use InvExportLabel\Service\TypeInstanceInterface;
use InvMixerProduct\Helper\OrderLineItemEntityAccessor;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderCollection;

/**
 * Class TypeInstance
 * @package InvExportLabel\Service\TypeInstance\MixerProduct
 */
class TypeInstance implements TypeInstanceInterface
{
    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * TypeInstance constructor.
     * @param Renderer $renderer
     */
    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
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
            foreach ($orderEntity->getLineItems() as $lineItem) {
                if (true === $this->isLineItemWithSubject($lineItem)) {
                    $filteredCollection->add($lineItem);
                }
            }
        }

        return $filteredCollection;
    }

    /**
     * @param OrderLineItemEntity $orderLineItemEntity
     * @return bool
     */
    private function isLineItemWithSubject(OrderLineItemEntity $orderLineItemEntity): bool
    {

        if (true !== OrderLineItemEntityAccessor::isContainsMixContainerProduct($orderLineItemEntity)) {
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
