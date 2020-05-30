<?php declare(strict_types=1);

namespace InvMixerProduct\Value;

/**
 * Class MixContainer
 * @package InvMixerProduct\Value
 */
class MixContainer {

    /**
     * @var Design
     */
    private $design;


    /**
     * @var Weight
     */
    private $maxContainerWeight;

    /**
     * MixContainer constructor.
     * @param Design $design
     * @param Weight $maxContainerWeight
     */
    public function __construct(Design $design, Weight $maxContainerWeight)
    {
        $this->design = $design;
        $this->maxContainerWeight = $maxContainerWeight;
    }

    /**
     * @return static
     */
    public static function aDefault():self {
        return new self(
            Design::aDefault(),
            Weight::xGrams(200)
        );
    }

    /**
     * @return Design
     */
    public function getDesign(): Design
    {
        return $this->design;
    }

    /**
     * @return Weight
     */
    public function getMaxContainerWeight(): Weight
    {
        return $this->maxContainerWeight;
    }




}
