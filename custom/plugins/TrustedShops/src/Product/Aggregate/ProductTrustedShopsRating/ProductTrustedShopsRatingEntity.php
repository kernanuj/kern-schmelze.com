<?php declare(strict_types=1);

namespace TrustedShops\Product\Aggregate\ProductTrustedShopsRating;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ProductTrustedShopsRatingEntity extends Entity
{
    use EntityIdTrait;

    /**
     * @var string
     */
    protected $productId;

    /**
     * @var float|null
     */
    protected $overallMark;

    /**
     * @var ProductEntity|null
     */
    protected $product;


    public function getProductId(): string
    {
        return $this->productId;
    }

    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    public function getOverallMark(): ?float
    {
        return $this->overallMark;
    }

    public function setOverallMark(?float $mark): void
    {
        $this->overallMark = $mark;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

}