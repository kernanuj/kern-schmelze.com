<?php declare(strict_types=1);

namespace InvMixerProduct\Value;

use InvMixerProduct\Constants;

/**
 * Class Design
 * @package InvMixerProduct\Value
 */
class Design
{

    /**
     * @var string
     */
    private $identifier;

    /**
     * Design constructor.
     * @param string $identifier
     */
    private function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return static
     */
    public static function aDefault(): self
    {
        return new self(Constants::DESIGN_WHITE);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->identifier;
    }


}
