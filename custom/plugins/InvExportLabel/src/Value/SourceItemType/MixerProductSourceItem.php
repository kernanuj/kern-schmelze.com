<?php declare(strict_types=1);


namespace InvExportLabel\Value\SourceItemType;

use InvExportLabel\Value\SourceItemInterface;

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
     * @var string
     */
    private $ingredients = '';

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
     * @return string
     */
    public function getIngredients(): string
    {
        return $this->ingredients;
    }

    /**
     * @param string $ingredients
     * @return MixerProductSourceItem
     */
    public function setIngredients(string $ingredients): MixerProductSourceItem
    {
        $this->ingredients = $ingredients;
        return $this;
    }


}
