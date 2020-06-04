<?php declare(strict_types=1);

namespace InvMixerProduct\Value;

/**
 * Class Label
 * @package InvMixerProduct\Value
 */
class Label
{

    /**
     * @var string|null
     */
    private $value;

    /**
     * Label constructor.
     * @param string|null $value
     */
    public function __construct(?string $value)
    {
        $this->value = $value;
    }

    /**
     * @return $this
     */
    public static function aEmpty(): self
    {
        return new self('');
    }

    /**
     * @param string|null $value
     * @return static
     */
    public static function fromString(?string $value):self {
        return new self($value);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value ?: '';
    }


}
