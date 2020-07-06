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
     * @param float $value
     * @return static
     */
    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

    /**
     * @param Price $left
     * @return $this
     */
    public function add(Price $left): self
    {
        return new self(
            $this->value + $left->getValue()
        );
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @param int $factor
     * @return $this
     */
    public function multipliedBy(int $factor): self
    {
        return new self(
            $this->value * $factor
        );
    }

}
