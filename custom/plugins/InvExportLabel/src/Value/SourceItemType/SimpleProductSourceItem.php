<?php declare(strict_types=1);


namespace InvExportLabel\Value\SourceItemType;

use InvExportLabel\Constants;
use InvExportLabel\Value\SourceItemInterface;
use InvMixerProduct\Value\Weight;

/**
 * Class SimpleProductSourceItem
 * @package InvExportLabel\Value\SourceItemType
 */
class SimpleProductSourceItem implements SourceItemInterface
{


    /**
     * @var $productName
     */
    private $productName;

    /**
     * @var array
     */
    private $ingredients = [];

    /**
     * @var \DateTime
     */
    private $bestBeforeDate;

    /**
     * @var Weight
     */
    private $weight;

    /**
     * @var string
     */
    private $orderNumber;

    /**
     * @return mixed
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * @param mixed $productName
     * @return SimpleProductSourceItem
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return Constants::LABEL_TYPE_SIMPLE_PRODUCT;
    }

    /**
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     * @return SimpleProductSourceItem
     */
    public function setOrderNumber(string $orderNumber): SourceItemInterface
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    /**
     * @return array
     */
    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    /**
     * @param array $ingredients
     * @return SimpleProductSourceItem
     */
    public function setIngredients(array $ingredients): SimpleProductSourceItem
    {
        $this->ingredients = $ingredients;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBestBeforeDate(): \DateTime
    {
        return $this->bestBeforeDate;
    }

    /**
     * @param \DateTime $bestBeforeDate
     * @return SimpleProductSourceItem
     */
    public function setBestBeforeDate(\DateTime $bestBeforeDate): SimpleProductSourceItem
    {
        $this->bestBeforeDate = $bestBeforeDate;
        return $this;
    }

    /**
     * @return Weight
     */
    public function getWeight(): Weight
    {
        return $this->weight;
    }

    /**
     * @param Weight $weight
     * @return SimpleProductSourceItem
     */
    public function setWeight(Weight $weight): SimpleProductSourceItem
    {
        $this->weight = $weight;
        return $this;
    }


}
