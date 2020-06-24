<?php declare(strict_types=1);


namespace InvExportLabel\Value;

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
     * @return SourceItemInterface[]
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





}
