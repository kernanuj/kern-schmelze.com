<?php declare(strict_types=1);

namespace InvMixerProduct\Value;

use InvMixerProduct\Constants;
use Shopware\Core\Framework\Struct;

/**
 * Class Weight
 * @package InvMixerProduct\Value
 */
class Weight extends Struct\Struct
{

    /**
     * @var float
     */
    protected $value;

    /**
     * @var string
     */
    protected $unit;

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
        return self::xGrams(0);
    }

    /**
     * @param int $grams
     * @return $this
     */
    public static function xGrams(int $grams): self
    {
        return new self(
            $grams,
            Constants::WEIGHT_UNIT_GRAMS
        );
    }

    /**
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['value'],
            $data['unit']
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
     * @param Weight $left
     * @return bool
     */
    public function isEqualTo(Weight $left): bool
    {
        return $this->getValue() === $left->getValue() && $this->getUnit() === $left->getUnit();
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getUnit(): string
    {
        return $this->unit;
    }

}
