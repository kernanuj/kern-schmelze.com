<?php declare(strict_types=1);

namespace InvMixerProduct\Value;

use Shopware\Core\Framework\Struct\Struct;

/**
 * Class Design
 * @package InvMixerProduct\Value
 */
class BaseProduct extends Struct
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
     * @param BaseProduct $left
     * @return bool
     */
    public function isEqualTo(BaseProduct $left): bool
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
