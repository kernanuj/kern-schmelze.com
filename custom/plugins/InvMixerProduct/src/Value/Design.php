<?php declare(strict_types=1);

namespace InvMixerProduct\Value;

use InvMixerProduct\Constants;
use Shopware\Core\Framework\Struct\Struct;

/**
 * Class Design
 * @package InvMixerProduct\Value
 */
class Design extends Struct
{

    /**
     * @var string
     */
    public $identifier;

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

    /**
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data):self {
        return new self($data['identifier']);
    }


}
