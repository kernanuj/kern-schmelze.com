<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Constants;
use InvMixerProduct\Entity\MixEntity as Subject;
use InvMixerProduct\Repository\ProductRepository;
use InvMixerProduct\Repository\SalesChannelProductRepository;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\Cart\ProductLineItemFactory;
use Shopware\Core\Framework\Uuid\Uuid;
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
     * @var SalesChannelProductRepository
     */
    private $salesChannelProductRepository;

    /**
     * ContainerMixToCartItemConverter constructor.
     * @param ProductLineItemFactory $productLineItemFactory
     * @param ProductRepository $productRepository
     * @param SalesChannelProductRepository $salesChannelProductRepository
     */
    public function __construct(
        ProductLineItemFactory $productLineItemFactory,
        ProductRepository $productRepository,
        SalesChannelProductRepository $salesChannelProductRepository
    ) {
        $this->productLineItemFactory = $productLineItemFactory;
        $this->productRepository = $productRepository;
        $this->salesChannelProductRepository = $salesChannelProductRepository;
    }

    /**
     * @inheritDoc
     * @throws \InvMixerProduct\Exception\EntityNotFoundException
     */
    public function toCartItem(
        Subject $subject,
        int $quantity,
        SalesChannelContext $salesChannelContext
    ): LineItem {


        $baseProduct = $this->salesChannelProductRepository->findOneByProductNumber(
            $subject->getContainerDefinition()->translateToProductNumber(),
            $salesChannelContext
        );


        $lineItem = null;
        $lineItem = new LineItem(
            $subject->getId(),
            Constants::LINE_ITEM_TYPE_IDENTIFIER,
            null,
            $quantity
        );
        $lineItem->setRemovable(true);
        $lineItem->setStackable(true);
        $lineItem->setPayloadValue(Constants::KEY_MIX_LABEL_CART_ITEM, $subject->getLabel());
        $lineItem->setPayloadValue(Constants::KEY_IS_MIX_CONTAINER_PRODUCT, true);
        $lineItem->setPayloadValue(Constants::KEY_MIX_ENTITY_ID, $subject->getId());
        $lineItem->setPayloadValue(Constants::KEY_MIX_DISPLAY_ID, $subject->getDisplayId());


        // add container product as first line item
        $baseProductLineItem = new LineItem(
            Uuid::randomHex(),
            LineItem::PRODUCT_LINE_ITEM_TYPE,
            $baseProduct->getId(),
            $quantity
        );
        $baseProductLineItem->setPayloadValue(Constants::KEY_IS_MIX_BASE_PRODUCT, true);
        $baseProductLineItem->setPayloadValue(Constants::KEY_MIX_ENTITY_ID, $subject->getId());
        $lineItem->addChild(
            $baseProductLineItem
        );

        foreach ($subject->getItems() as $item) {
            $childLineItem = new LineItem(
                $item->getId(),
                LineItem::PRODUCT_LINE_ITEM_TYPE,
                $item->getProductId(),
                $item->getQuantity() * $quantity
            );

            $childLineItem->setRemovable(false);
            $childLineItem->setStackable(false);
            $childLineItem->setPayloadValue(Constants::KEY_IS_MIX_CHILD_PRODUCT, true);
            $childLineItem->setPayloadValue(Constants::KEY_MIX_ENTITY_ID, $subject->getId());
            $childLineItem->setPayloadValue(Constants::KEY_MIX_ITEM_ENTITY_ID, $item->getId());

            $lineItem->addChild(
                $childLineItem
            );

        }

        return $lineItem;
    }

}
