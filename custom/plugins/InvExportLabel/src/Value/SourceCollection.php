<?php declare(strict_types=1);


namespace InvExportLabel\Value;

use InvExportLabel\Value\SourceItemType\MixerProductSourceItem;
use Shopware\Core\Checkout\Order\OrderCollection;

/**
 * Class SourceCollection
 * @package InvExportLabel\Value
 */
class SourceCollection
{

    /**
     * @var SourceItemInterface[]
     */
    private $items = [];

    /**
     * @var OrderCollection
     */
    private $orderCollection;


    /**
     * @return SourceItemInterface[]|MixerProductSourceItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param SourceItemInterface $item
     * @return $this
     */
    public function addItem(SourceItemInterface $item): SourceCollection
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasItems():bool {
        return count($this->items) > 0;
    }

    /**
     * @return OrderCollection
     */
    public function getOrderCollection(): OrderCollection
    {
        return $this->orderCollection;
    }

    /**
     * @param OrderCollection $orderCollection
     * @return SourceCollection
     */
    public function setOrderCollection(OrderCollection $orderCollection): SourceCollection
    {
        $this->orderCollection = $orderCollection;
        return $this;
    }

}
