<?php declare(strict_types=1);

namespace InvMixerProduct\Struct;

use InvMixerProduct\Exception\EntityNotFoundException;
use InvMixerProduct\Value\BaseProduct;
use InvMixerProduct\Value\Design;
use InvMixerProduct\Value\Weight;

/**
 * Class ContainerDefinitionCollection
 * @package InvMixerProduct\Struct
 */
class ContainerDefinitionCollection implements \Iterator
{

    /**
     * @var ContainerDefinition[]
     */
    private $items;

    /**
     * @var int
     */
    private $pointer = 0;

    /**
     * @param ContainerDefinition $item
     * @return $this
     */
    public function addItem(ContainerDefinition $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->items[$this->pointer];
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->pointer++;
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->pointer;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return isset($this->items[$this->pointer]);
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->pointer = 0;
    }

    /**
     * @return Design[]
     */
    public function getAvailableDesigns(): array
    {
        $designs = [];

        foreach ($this->items as $item) {
            $designs[] = $item->getDesign();
        }

        return array_unique($designs);
    }

    /**
     * @return Weight[]
     */
    public function getAvailableMaxWeights(): array
    {
        $weights = [];

        foreach ($this->items as $item) {
            $weights[] = $item->getFillDelimiter()->getWeight();
        }

        return array_unique($weights);
    }

    /**
     * @return BaseProduct[]
     */
    public function getAvailableBaseProducts(): array
    {
        $items = [];

        foreach ($this->items as $item) {
            $items[$item->getBaseProduct()->getIdentifier()] = $item->getBaseProduct();
        }

        return $items;
    }

    /**
     * @param Weight $weight
     * @param Design $design
     * @return ContainerDefinition
     *
     * @throws EntityNotFoundException
     */
    public function oneOfWeightAndDesign(
        Weight $weight,
        Design $design
    ): ContainerDefinition {

        foreach ($this->items as $item) {
            if ($item->getFillDelimiter()->getWeight()->isEqualTo($weight) && $item->getDesign()->isEqualTo($design)) {
                return $item;
            }
        }

        throw EntityNotFoundException::fromEntityAndIdentifier(
            ContainerDefinition::class,
            $weight . $design
        );
    }

    /**
     * @param Weight $weight
     * @param Design $design
     * @param BaseProduct $baseProduct
     * @return ContainerDefinition
     * @throws EntityNotFoundException
     */
    public function oneOfWeightDesignAndBaseProduct(
        Weight $weight,
        Design $design,
        BaseProduct $baseProduct
    ): ContainerDefinition {

        foreach ($this->items as $item) {
            if ($item->getFillDelimiter()->getWeight()->isEqualTo($weight) && $item->getDesign()->isEqualTo($design) && $item->getBaseProduct()->isEqualTo($baseProduct)) {
                return $item;
            }
        }

        throw EntityNotFoundException::fromEntityAndIdentifier(
            ContainerDefinition::class,
            $weight . $design. $baseProduct
        );
    }


}
