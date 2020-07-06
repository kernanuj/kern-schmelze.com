<?php declare(strict_types=1);


namespace InvMixerProduct\DataObject;

/**
 * Class MixViewItemCollection
 * @package InvMixerProduct\DataObject
 */
class MixViewItemCollection
{

    /**
     * @var MixViewItem[]
     */
    private $items = [];

    /**
     * @return MixViewItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param MixViewItem $item
     * @return $this
     */
    public function addItem(MixViewItem $item): self
    {
        $this->items[] = $item;

        return $this;
    }

}
