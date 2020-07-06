<?php declare(strict_types=1);


namespace InvMixerProduct\DataObject;

use InvMixerProduct\Entity\MixItemEntity;
use InvMixerProduct\Value\Price;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\CalculatedListingPrice;
use Shopware\Core\System\Unit\UnitEntity;

class MixViewItem
{


    /**
     * @var MixItemEntity
     */
    private $referencedMixItem;

    /**
     * @var SalesChannelProductEntity
     */
    private $salesChannelProduct;

    /**
     * MixViewItem constructor.
     * @param MixItemEntity $referencedMixItem
     * @param SalesChannelProductEntity $salesChannelProduct
     */
    public function __construct(MixItemEntity $referencedMixItem, SalesChannelProductEntity $salesChannelProduct)
    {
        $this->referencedMixItem = $referencedMixItem;
        $this->salesChannelProduct = $salesChannelProduct;
    }

    /**
     * @return MixItemEntity
     */
    public function getReferencedMixItem(): MixItemEntity
    {
        return $this->referencedMixItem;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->referencedMixItem->getQuantity();
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->referencedMixItem->getProductId();
    }

    /**
     * @return UnitEntity
     */
    public function getUnitEntity(): UnitEntity
    {
        return $this->referencedMixItem->getProduct()->getUnit();
    }

    /**
     * @return MediaEntity|null
     */
    public function getCover(): MediaEntity
    {
        return $this->referencedMixItem->getProduct()->getCover()->getMedia();
    }

    /**
     * @return Price
     */
    public function listingPrice(): Price
    {
        return Price::fromFloat($this->getCalculatedListingPrice()->getFrom()->getTotalPrice());
    }

    /**
     * @return CalculatedListingPrice
     */
    public function getCalculatedListingPrice(): CalculatedListingPrice
    {
        return $this->salesChannelProduct->getCalculatedListingPrice();
    }

}
