<?php declare(strict_types=1);

namespace InvMixerProduct\Value;

use InvMixerProduct\Constants;

/**
 * Class Weight
 * @package InvMixerProduct\Value
 */
class Weight
{

    /**
     * @var float
     */
    private $value;

    /**
     * @var string
     */
    private $unit;

    /**
     * Weight constructor.
     * @param float|int $value
     * @param string $unit
     */
    private function __construct($value, string $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    /**
     * @return Weight
     */
    public static function aZeroGrams()
    {
        return new self (
            0,
            Constants::WEIGHT_UNIT_GRAMS
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value . ' ' . $this->unit;
    }

    /**
     * @param int $grams
     * @return $this
     */
    public static function xGrams(int $grams):self {
        return new self(
          $grams,
          Constants::WEIGHT_UNIT_GRAMS
        );
    }


}
