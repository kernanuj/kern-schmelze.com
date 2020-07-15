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


        $containerProduct = $this->salesChannelProductRepository->findOneByProductNumber(
            $mix->getContainerDefinition()->translateToProductNumber(),
            $salesChannelContext
        );

        $itemCollection = $this->buildItemCollection($salesChannelContext, $mix);
        $mixView = new MixView(
            Identifier::fromString($mix->getId()),
            Label::fromString($mix->getLabel()),
            $this->getTotalPrice(
                $salesChannelContext,
                $itemCollection,
                $containerProduct
            ),
            $this->getTotalWeight(
                $salesChannelContext,
                $mix,
                $containerProduct
            ),
            $mix->getContainerDefinition(),
            $mix->getCustomer(),
            $itemCollection
        );

        $mixView->setIsFilled(
            $mix->getContainerDefinition()->getFillDelimiter()->getAmount()->getValue() == $mix->getTotalItemQuantity()
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
     * @param SalesChannelProductEntity $containerProduct
     * @return Price
     */
    private function getTotalPrice(
        SalesChannelContext $salesChannelContext,
        MixViewItemCollection $mixViewItemCollection,
        SalesChannelProductEntity $containerProduct
    ): Price {
        $price = Price::aZero();

        foreach ($mixViewItemCollection->getItems() as $item) {
            $price = $price->add(
                $item->listingPrice()->multipliedBy($item->getQuantity())
            );
        }

        $price = $price->add(
            Price::fromFloat($containerProduct->getCalculatedListingPrice()->getFrom()->getTotalPrice())
        );

        return $price;

    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param Subject $mixEntity
     * @param SalesChannelProductEntity $containerProduct
     * @return Weight
     */
    private function getTotalWeight(
        SalesChannelContext $salesChannelContext,
        MixEntity $mixEntity,
        SalesChannelProductEntity $containerProduct
    ): Weight {

        return $mixEntity->getContainerDefinition()->getFillDelimiter()->getWeight();
        //#40 the weight of the product is completely determined by the weight of the container product; each ingredient will only substitute a part of the weight
        /**
         * $weight = Weight::aZeroGrams();
         *
         * if ($mixEntity->hasItems()) {
         * foreach ($mixEntity->getItems() as $item) {
         * $weight->add(
         * $this->productAccessor->accessProductWeight(
         * $item->getProduct(),
         * $salesChannelContext
         * )->multipliedBy($item->getQuantity())
         * );
         * }
         * }
         *
         * return $weight;
         * **/
    }

}
