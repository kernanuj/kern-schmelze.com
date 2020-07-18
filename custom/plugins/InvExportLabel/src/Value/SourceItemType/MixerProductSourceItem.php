<?php declare(strict_types=1);


namespace InvExportLabel\Value\SourceItemType;

use InvExportLabel\Value\SourceItemInterface;
use InvMixerProduct\Value\Weight;

/**
 * Class MixerProductSourceItem
 * @package InvExportLabel\Value\SourceItemType
 */
class MixerProductSourceItem implements SourceItemInterface
{


    /**
     * @var string
     */
    private $mixName = '';

    /**
     * @var array
     */
    private $ingredients = [];

    /**
     * @var \DateTime
     */
    private $bestBeforeDate;

    /**
     * @var string
     */
    private $displayId = 0;

    /**
     * @var Weight
     */
    private $weight;


    /**
     * @return string
     */
    public function getMixName(): string
    {
        return $this->mixName;
    }

    /**
     * @param string $mixName
     * @return MixerProductSourceItem
     */
    public function setMixName(string $mixName): MixerProductSourceItem
    {
        $this->mixName = $mixName;
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
     * @return MixerProductSourceItem
     */
    public function setIngredients(array $ingredients): MixerProductSourceItem
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
     * @return MixerProductSourceItem
     */
    public function setBestBeforeDate(\DateTime $bestBeforeDate): MixerProductSourceItem
    {
        $this->bestBeforeDate = $bestBeforeDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayId(): string
    {
        return $this->displayId;
    }

    /**
     * @param string $displayId
     * @return MixerProductSourceItem
     */
    public function setDisplayId(string $displayId): MixerProductSourceItem
    {
        $this->displayId = $displayId;
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
     * @return MixerProductSourceItem
     */
    public function setWeight(Weight $weight): MixerProductSourceItem
    {
        $this->weight = $weight;
        return $this;
    }


}
