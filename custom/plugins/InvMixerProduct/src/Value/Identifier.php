<?php declare(strict_types=1);

namespace InvMixerProduct\Value;

use InvMixerProduct\Constants;

/**
 * Class Identifier
 * @package InvMixerProduct\Value
 */
class Identifier {

    /**
     * @var string
     */
    private $value;

    /**
     * Identifier constructor.
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param string $value
     * @return static
     */
    public static function fromString(string $value):self {
        return new self($value);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

}
