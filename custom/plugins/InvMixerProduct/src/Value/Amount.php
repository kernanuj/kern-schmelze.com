<?php declare(strict_types=1);

namespace InvMixerProduct\Value;

use Shopware\Core\Framework\Struct;

/**
 * Class Amount
 * @package InvMixerProduct\Value
 */
class Amount extends Struct\Struct
{

    /**
     * @var int
     */
    protected $value;

    /**
     * Amount constructor.
     * @param int $value
     */
    public function __construct(int $value)
    {
        $this->value = $value;
    }


    /**
     * @param int $value
     * @return static
     */
    public static function fromInt(int $value): self
    {
        return new self(
            $value
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value . ' ' . 'pieces';
    }

    /**
     * @param Amount $left
     * @return bool
     */
    public function isEqualTo(Amount $left): bool
    {
        return $this->getValue() === $left->getValue();
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        return new self((int)$data['value']);
    }


    /**
     * @param Weight $left
     * @return bool
     */
    public function isGreaterThan(Weight $left): bool
    {
        return $this->getValue() > $left->getValue();
    }


}
