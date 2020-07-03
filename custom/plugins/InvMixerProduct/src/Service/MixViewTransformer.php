<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\DataObject\MixView;
use InvMixerProduct\DataObject\MixViewItem;
use InvMixerProduct\DataObject\MixViewItemCollection;
use InvMixerProduct\Entity\MixEntity;
use InvMixerProduct\Entity\MixEntity as Subject;
use InvMixerProduct\Entity\MixItemEntity;
use InvMixerProduct\Value\Identifier;
use InvMixerProduct\Value\Label;
use InvMixerProduct\Value\Price;
use InvMixerProduct\Value\Weight;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class MixViewTransformer
 * @package InvMixerProduct\Service
 */
class MixViewTransformer
{

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param Subject $mix
     * @return MixView
     */
    public function transform(
        SalesChannelContext $salesChannelContext,
        MixEntity $mix
    ): MixView {


        $itemCollection = $this->buildItemCollection($salesChannelContext, $mix);
        return new MixView(
            Identifier::fromString($mix->getId()),
            Label::fromString($mix->getLabel()),
            Price::aZero(),
            Weight::aZeroGrams(),
            $mix->getContainerDefinition(),
            $mix->getCustomer(),
            $itemCollection
        );
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param Subject $mix
     * @return MixViewItemCollection
     */
    private function buildItemCollection(
        SalesChannelContext $salesChannelContext,
        MixEntity $mix
    ): MixViewItemCollection {

        $collection = new MixViewItemCollection();

        foreach ($mix->getItems() as $item) {
            $collection->addItem(
                $this->buildMixViewItem($salesChannelContext, $item)
            );
        }
        return $collection;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param MixItemEntity $itemEntity
     * @return MixViewItem
     */
    private function buildMixViewItem(
        SalesChannelContext $salesChannelContext,
        MixItemEntity $itemEntity
    ): MixViewItem {

        return new MixViewItem($itemEntity);
    }

}
