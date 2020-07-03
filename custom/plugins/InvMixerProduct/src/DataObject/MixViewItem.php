<?php declare(strict_types=1);


namespace InvMixerProduct\DataObject;

use InvMixerProduct\Entity\MixItemEntity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\System\Unit\UnitEntity;

class MixViewItem
{


    /**
     * @var MixItemEntity
     */
    private $referencedMixItem;


    /**
     * MixViewItem constructor.
     * @param MixItemEntity $referencedMixItem
     */
    public function __construct(MixItemEntity $referencedMixItem)
    {
        $this->referencedMixItem = $referencedMixItem;
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
}
