<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Constants;
use InvMixerProduct\Entity\MixEntity as Subject;
use InvMixerProduct\Repository\ProductRepository;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\Cart\ProductLineItemFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 *
 * This service converts a mix to a cart item that is based on the custom implementation of using a "container" product.
 * Is still POC state and may not be the final way to handle mixes in carts.
 *
 * Interface MixToCartItemConverterInterface
 * @package InvMixerProduct\Service
 */
class ContainerMixToCartItemConverter implements MixToCartItemConverterInterface
{

    /**
     * @var ProductLineItemFactory
     */
    private $productLineItemFactory;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * ContainerMixToCartItemConverter constructor.
     * @param ProductLineItemFactory $productLineItemFactory
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ProductLineItemFactory $productLineItemFactory,
        ProductRepository $productRepository
    ) {
        $this->productLineItemFactory = $productLineItemFactory;
        $this->productRepository = $productRepository;
    }


    /**
     * @inheritDoc
     */
    public function toCartItem(
        Subject $subject,
        int $quantity,
        SalesChannelContext $salesChannelContext
    ): LineItem {


        $containerProduct = $this->productRepository->findOneByProductNumber(
            'ks-mixer-container',
            $salesChannelContext->getContext()
        );

        $lineItem = null;
        $lineItem = new LineItem($subject->getId(), Constants::LINE_ITEM_TYPE_IDENTIFIER, $containerProduct->getId(),
            $quantity);
        $lineItem->setRemovable(true);
        $lineItem->setStackable(true);
        $lineItem->setPayloadValue(Constants::KEY_MIX_LABEL_CART_ITEM, $subject->getLabel());
        $lineItem->setPayloadValue(Constants::KEY_IS_MIX_CONTAINER_PRODUCT, true);

        foreach ($subject->getItems() as $item) {
            $childLineItem = new LineItem(
                $item->getId(),
                LineItem::PRODUCT_LINE_ITEM_TYPE,
                $item->getProductId(),
                $item->getQuantity()
            );

            $childLineItem->setRemovable(false);
            $childLineItem->setStackable(false);

            $lineItem->addChild(
                $childLineItem
            );

        }

        return $lineItem;
    }

}
