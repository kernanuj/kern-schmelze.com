<?php declare(strict_types=1);

namespace InvMixerProduct\Value;

/**
 * Class Label
 * @package InvMixerProduct\Value
 */
class Price
{

    /**
     * @var float
     */
    private $value;

    /**
     * Price constructor.
     * @param float $value
     */
    private function __construct(float $value)
    {
        $this->value = $value;
    }


    /**
     * @return $this
     */
    public static function aZero(): self
    {
        return new self(0);
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
    public function __toString()
    {
     return (string)$this->value;
    }

}
