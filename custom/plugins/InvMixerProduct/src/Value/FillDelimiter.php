<?php declare(strict_types=1);

namespace InvMixerProduct\Value;

use Shopware\Core\Framework\Struct;
use function assert;

/**
 * Class FillDelimiter
 * @package InvMixerProduct\Value
 */
class FillDelimiter extends Struct\Struct
{

    /**
     * @var Weight
     */
    protected $weight;

    /**
     * @var Amount
     */
    protected $amount;

    /**
     * FillDelimiter constructor.
     * @param Weight $weight
     * @param Amount $amount
     */
    public function __construct(Weight $weight, Amount $amount)
    {
        $this->weight = $weight;
        $this->amount = $amount;
    }

    /**
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Weight::fromArray($data['weight']),
            Amount::fromArray($data['amount'])
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->weight . ' ' . (string)$this->amount;
    }

    /**
     * @return Weight
     */
    public function getWeight(): Weight
    {
        return $this->weight;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->amount;
    }



}
