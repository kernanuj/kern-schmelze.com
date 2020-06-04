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
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        return new self($data['identifier']);
    }

    /**
     * @param string $data
     * @return static
     */
    public static function fromString(string $data): self
    {
        return new self($data);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->identifier;
    }

    /**
     * @param Design $left
     * @return bool
     */
    public function isEqualTo(Design $left): bool
    {
        return $this->getIdentifier() === $left->getIdentifier();
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }


}
