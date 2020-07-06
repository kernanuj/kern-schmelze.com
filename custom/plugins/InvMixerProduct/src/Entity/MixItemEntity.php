<?php declare(strict_types=1);

namespace InvMixerProduct\Entity;

use InvMixerProduct\Value\Weight;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

/**
 * Class MixItemEntity
 * @package InvMixerProduct\Entity
 */
class MixItemEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var MixEntity
     */
    protected $mix;

    /**
     * @var string
     */
    protected $mixId;

    /**
     * @var ProductEntity
     */
    protected $product;

    /**
     * @var string
     */
    protected $productId;

    /**
     * @var int
     */
    protected $quantity;

    /**
     * @return MixEntity
     */
    public function getMix(): MixEntity
    {
        return $this->mix;
    }

    /**
     * @param MixEntity $mix
     * @return MixItemEntity
     */
    public function setMix(MixEntity $mix): MixItemEntity
    {
        $this->mix = $mix;
        $this->mixId = $mix->getId();

        return $this;
    }

    /**
     * @return ProductEntity
     */
    public function getProduct(): ProductEntity
    {
        return $this->product;
    }

    /**
     * @param ProductEntity $product
     * @return MixItemEntity
     */
    public function setProduct(ProductEntity $product): MixItemEntity
    {
        $this->product = $product;
        $this->productId = $product->getId();

        return $this;
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return MixItemEntity
     */
    public function setQuantity(int $quantity): MixItemEntity
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return string
     */
    public function getMixId(): string
    {
        return $this->mixId;
    }
}
