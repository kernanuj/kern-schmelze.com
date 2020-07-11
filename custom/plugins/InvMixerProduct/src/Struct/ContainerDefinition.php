<?php declare(strict_types=1);

namespace InvMixerProduct\Struct;

use InvMixerProduct\Value\BaseProduct;
use InvMixerProduct\Value\Design;
use InvMixerProduct\Value\FillDelimiter;
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
     * @var BaseProduct
     */
    protected $baseProduct;

    /**
     * @var FillDelimiter
     */
    protected $fillDelimiter;

    /**
     * ContainerDefinition constructor.
     * @param Design $design
     * @param BaseProduct $baseProduct
     * @param FillDelimiter $fillDelimiter
     */
    public function __construct(Design $design, BaseProduct $baseProduct, FillDelimiter $fillDelimiter)
    {
        $this->design = $design;
        $this->baseProduct = $baseProduct;
        $this->fillDelimiter = $fillDelimiter;
    }


    /**
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        return new self(
            Design::fromArray($data['design']),
            BaseProduct::fromArray($data['baseProduct']),
            FillDelimiter::fromArray($data['fillDelimiter'])
        );
    }

    /**
     * @param Design $design
     * @param BaseProduct $baseProduct
     * @param FillDelimiter $fillDelimiter
     * @return static
     */
    public static function build(
        Design $design,
        BaseProduct $baseProduct,
        FillDelimiter $fillDelimiter
    ): self {
        return new self(
            $design,
            $baseProduct,
            $fillDelimiter
        );
    }

    /**
     * @return string
     */
    public function translateToProductNumber(): string
    {
        return strtolower(sprintf(
                'impc_%s_%s_%s',
                $this->getFillDelimiter()->getWeight()->getValue() . $this->getFillDelimiter()->getWeight()->getUnit(),
                $this->getDesign(),
                $this->getBaseProduct()->getIdentifier()
            )
        );
    }

    /**
     * @return FillDelimiter
     */
    public function getFillDelimiter(): FillDelimiter
    {
        return $this->fillDelimiter;
    }

    /**
     * @return Design
     */
    public function getDesign(): Design
    {
        return $this->design;
    }

    /**
     * @return BaseProduct
     */
    public function getBaseProduct(): BaseProduct
    {
        return $this->baseProduct;
    }

    public function jsonSerialize(): array
    {
        $data = [];
        $data['design'] = $this->design->jsonSerialize();
        $data['baseProduct'] = $this->baseProduct->jsonSerialize();
        $data['fillDelimiter'] = $this->fillDelimiter->jsonSerialize();


        return $data;
    }

}
