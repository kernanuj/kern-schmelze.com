<?php declare(strict_types=1);

namespace InvMixerProduct\Struct;

use InvMixerProduct\Value\Design;
use InvMixerProduct\Value\Weight;
use Shopware\Core\Framework\Struct\Struct;

/**
 * Class ContainerDefinition
 * @package InvMixerProduct\Struct
 */
class ContainerDefinition extends Struct
{

    /**
     * @var Design
     */
    protected $design;

    /**
     * @var Weight
     */
    protected $maxContainerWeight;

    /**
     * @var int
     */
    protected $maximumNumberOfProducts;

    /**
     * ContainerDefinition constructor.
     * @param Design $design
     * @param Weight $maxContainerWeight
     * @param int $maximumNumberOfProducts
     */
    private function __construct(
        Design $design,
        Weight $maxContainerWeight,
        int $maximumNumberOfProducts
    ) {
        $this->design = $design;
        $this->maxContainerWeight = $maxContainerWeight;
        $this->maximumNumberOfProducts = $maximumNumberOfProducts;
    }


    /**
     * @return static
     */
    public static function aDefault(): self
    {
        return new self(
            Design::aDefault(),
            Weight::xGrams(200),
            10
        );
    }

    /**
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Design::fromArray($data['design']),
            Weight::fromArray($data['maxContainerWeight']),
            $data['maximumNumberOfProducts']
        );
    }

    /**
     * @param Design $design
     * @param Weight $weight
     * @param int $maxNumberOfProducts
     * @return static
     */
    public static function build(
        Design $design,
        Weight $weight,
        int $maxNumberOfProducts
    ): self {
        return new self(
            $design,
            $weight,
            $maxNumberOfProducts
        );
    }

    /**
     * @return Design
     */
    public function getDesign(): Design
    {
        return $this->design;
    }

    /**
     * @return Weight
     */
    public function getMaxContainerWeight(): Weight
    {
        return $this->maxContainerWeight;
    }

    /**
     * @return int
     */
    public function getMaximumNumberOfProducts(): int
    {
        return $this->maximumNumberOfProducts;
    }

    public function jsonSerialize(): array
    {
        $data = [];
        $data['design'] = $this->design->jsonSerialize();
        $data['maxContainerWeight'] = $this->maxContainerWeight->jsonSerialize();
        $data['maximumNumberOfProducts'] = $this->maximumNumberOfProducts;


        return $data;
    }

}
