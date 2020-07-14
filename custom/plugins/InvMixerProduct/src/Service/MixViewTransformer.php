<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\DataObject\MixView;
use InvMixerProduct\DataObject\MixViewItem;
use InvMixerProduct\DataObject\MixViewItemCollection;
use InvMixerProduct\Entity\MixEntity;
use InvMixerProduct\Entity\MixEntity as Subject;
use InvMixerProduct\Entity\MixItemEntity;
use InvMixerProduct\Exception\EntityNotFoundException;
use InvMixerProduct\Repository\SalesChannelProductRepository;
use InvMixerProduct\Value\Identifier;
use InvMixerProduct\Value\Label;
use InvMixerProduct\Value\Price;
use InvMixerProduct\Value\Weight;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
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
     * @var SalesChannelProductRepository
     */
    private $salesChannelProductRepository;

    /**
     * MixViewTransformer constructor.
     * @param ProductAccessorInterface $productAccessor
     * @param SalesChannelProductRepository $salesChannelProductRepository
     */
    public function __construct(
        ProductAccessorInterface $productAccessor,
        SalesChannelProductRepository $salesChannelProductRepository
    ) {
        $this->productAccessor = $productAccessor;
        $this->salesChannelProductRepository = $salesChannelProductRepository;
    }


    /**
     * @param SalesChannelContext $salesChannelContext
     * @param Subject $mix
     * @return MixView
     * @throws EntityNotFoundException
     */
    public function transform(
        SalesChannelContext $salesChannelContext,
        MixEntity $mix
    ): MixView {


        $itemCollection = $this->buildItemCollection($salesChannelContext, $mix);
        $mixView = new MixView(
            Identifier::fromString($mix->getId()),
            Label::fromString($mix->getLabel()),
            $this->getTotalPrice(
                $salesChannelContext,
                $itemCollection
            ),
            $this->getTotalWeight(
                $salesChannelContext,
                $mix
            ),
            $mix->getContainerDefinition(),
            $mix->getCustomer(),
            $itemCollection
        );

        $mixView->setIsFilled(
            $mix->getContainerDefinition()->getFillDelimiter()->getWeight()->isEqualTo($mixView->getMixTotalWeight())
        );

        $mixView->setIsComplete(
            $mixView->isFilled() && !empty($mixView->getMixLabel())
        );

        return $mixView;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param Subject $mix
     * @return MixViewItemCollection
     * @throws EntityNotFoundException
     */
    private function buildItemCollection(
        SalesChannelContext $salesChannelContext,
        MixEntity $mix
    ): MixViewItemCollection {

        $collection = new MixViewItemCollection();

        if ($mix->hasItems()) {
            foreach ($mix->getItems() as $item) {
                $collection->addItem(
                    $this->buildMixViewItem($salesChannelContext, $item)
                );
            }
        }
        return $collection;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param MixItemEntity $itemEntity
     * @return MixViewItem
     * @throws EntityNotFoundException
     */
    private function buildMixViewItem(
        SalesChannelContext $salesChannelContext,
        MixItemEntity $itemEntity
    ): MixViewItem {

        return new MixViewItem(
            $itemEntity,
            $this->getSalesChannelProduct(
                $salesChannelContext,
                $itemEntity
            )
        );
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param MixItemEntity $mixItemEntity
     * @return SalesChannelProductEntity
     * @throws EntityNotFoundException
     */
    private function getSalesChannelProduct(
        SalesChannelContext $salesChannelContext,
        MixItemEntity $mixItemEntity
    ): SalesChannelProductEntity {

        return $this->salesChannelProductRepository->findOneById(
            $mixItemEntity->getProduct()->getId(),
            $salesChannelContext
        );

    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param MixViewItemCollection $mixViewItemCollection
     * @return Price
     */
    private function getTotalPrice(
        SalesChannelContext $salesChannelContext,
        MixViewItemCollection $mixViewItemCollection
    ): Price {
        $price = Price::aZero();

        foreach ($mixViewItemCollection->getItems() as $item) {
            $price = $price->add(
                $item->listingPrice()->multipliedBy($item->getQuantity())
            );
        }

        return $price;

    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param Subject $mixEntity
     * @return Weight
     */
    private function getTotalWeight(SalesChannelContext $salesChannelContext, MixEntity $mixEntity): Weight
    {
        $weight = Weight::aZeroGrams();

        if ($mixEntity->hasItems()) {
            foreach ($mixEntity->getItems() as $item) {
                $weight->add(
                    $this->productAccessor->accessProductWeight(
                        $item->getProduct(),
                        $salesChannelContext
                    )->multipliedBy($item->getQuantity())
                );
            }
        }
        return $weight;
    }

}
