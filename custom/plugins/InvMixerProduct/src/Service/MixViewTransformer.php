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
     * @var ProductAccessorInterface
     */
    private $productAccessor;

    /**
     * MixViewTransformer constructor.
     * @param ProductAccessorInterface $productAccessor
     */
    public function __construct(ProductAccessorInterface $productAccessor)
    {
        $this->productAccessor = $productAccessor;
    }

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
            $this->getTotalWeight(
                $salesChannelContext,
                $mix
            ),
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

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param Subject $mixEntity
     * @return Weight
     */
    private function getTotalWeight(SalesChannelContext $salesChannelContext, MixEntity $mixEntity): Weight
    {
        $weight = Weight::aZeroGrams();

        foreach ($mixEntity->getItems() as $item) {
            $weight->add(
                $this->productAccessor->accessProductWeight(
                    $item->getProduct(),
                    $salesChannelContext
                )->multipliedBy($item->getQuantity())
            );
        }

        return $weight;
    }

}
