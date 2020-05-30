<?php declare(strict_types=1);


namespace InvMixerProduct\DataObject;

use InvMixerProduct\Value\Identifier;
use InvMixerProduct\Value\Label;
use InvMixerProduct\Value\MixContainer;
use InvMixerProduct\Value\Weight;
use InvMixerProduct\Value\Price;

class MixView {

    /**
     * @var Identifier
     */
    private $mixId;

    /**
     * @var Label
     */
    private $mixLabel;

    /**
     * @var Price
     */
    private $mixTotalPrice;

    /**
     * @var Weight
     */
    private $mixTotalWeight;

    /**
     * @var MixContainer
     */
    private $mixContainer;

    /**
     * MixView constructor.
     * @param Identifier $mixId
     * @param Label $mixLabel
     * @param Price $mixTotalPrice
     * @param Weight $mixTotalWeight
     * @param MixContainer $mixContainer
     */
    public function __construct(
        Identifier $mixId,
        Label $mixLabel,
        Price $mixTotalPrice,
        Weight $mixTotalWeight,
        MixContainer $mixContainer
    ) {
        $this->mixId = $mixId;
        $this->mixLabel = $mixLabel;
        $this->mixTotalPrice = $mixTotalPrice;
        $this->mixTotalWeight = $mixTotalWeight;
        $this->mixContainer = $mixContainer;
    }


    /**
     * @return Identifier
     */
    public function getMixId(): Identifier
    {
        return $this->mixId;
    }

    /**
     * @return Label
     */
    public function getMixLabel(): Label
    {
        return $this->mixLabel;
    }

    /**
     * @return Price
     */
    public function getMixTotalPrice(): Price
    {
        return $this->mixTotalPrice;
    }

    /**
     * @return Weight
     */
    public function getMixTotalWeight(): Weight
    {
        return $this->mixTotalWeight;
    }

    /**
     * @return MixContainer
     */
    public function getMixContainer(): MixContainer
    {
        return $this->mixContainer;
    }






}
