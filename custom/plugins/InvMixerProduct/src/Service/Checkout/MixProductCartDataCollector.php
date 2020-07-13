<?php declare(strict_types=1);

namespace InvMixerProduct\Service\Checkout;

use InvMixerProduct\Constants;
use InvMixerProduct\Exception\EntityNotFoundException;
use InvMixerProduct\Helper\LineItemAccessor;
use InvMixerProduct\Repository\MixEntityRepository;
use InvMixerProduct\Repository\SalesChannelProductRepository;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryInformation;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryTime;
use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\LineItem\QuantityInformation;
use Shopware\Core\Content\Product\Cart\ProductCartProcessor;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class MixProductCartDataCollector
 * @package InvMixerProduct\Service\Checkout
 */
class MixProductCartDataCollector implements CartDataCollectorInterface
{
    /**
     * @var SalesChannelRepositoryInterface
     */
    private $productRepository;

    /**
     * @var MixEntityRepository
     */
    private $mixRepository;

    /**
     * @var SalesChannelProductRepository
     */
    private $salesChannelProductRepository;

    /**
     * MixProductCartDataCollector constructor.
     * @param SalesChannelRepositoryInterface $productRepository
     * @param MixEntityRepository $mixRepository
     * @param SalesChannelProductRepository $salesChannelProductRepository
     */
    public function __construct(
        SalesChannelRepositoryInterface $productRepository,
        MixEntityRepository $mixRepository,
        SalesChannelProductRepository $salesChannelProductRepository
    ) {
        $this->productRepository = $productRepository;
        $this->mixRepository = $mixRepository;
        $this->salesChannelProductRepository = $salesChannelProductRepository;
    }

    /**
     * @param CartDataCollection $data
     * @param Cart $original
     * @param SalesChannelContext $salesChannelContext
     * @param CartBehavior $behavior
     * @throws EntityNotFoundException
     */
    public function collect(
        CartDataCollection $data,
        Cart $original,
        SalesChannelContext $salesChannelContext,
        CartBehavior $behavior
    ): void {

        if ($behavior->hasPermission(ProductCartProcessor::SKIP_PRODUCT_RECALCULATION)) {
            return;
        }

        $subjectContainerProductLineItems = $original->getLineItems()->filterType(
            Constants::LINE_ITEM_TYPE_IDENTIFIER
        );

        if ($subjectContainerProductLineItems->count() === 0) {
            return;
        }

        foreach ($subjectContainerProductLineItems as $subjectContainerProductLineItem) {

            if (true !== LineItemAccessor::isContainsMixContainerProduct($subjectContainerProductLineItem)) {
                continue;
            }

            $this->addToCartData(
                $data,
                $subjectContainerProductLineItem,
                $salesChannelContext
            );

            $this->enrichSubjectProduct(
                $subjectContainerProductLineItem,
                $salesChannelContext,
                $original,
                $data
            );
        }
    }

    /**
     * @param CartDataCollection $data
     * @param LineItem $subjectContainerProductLineItem
     * @param SalesChannelContext $salesChannelContext
     * @throws EntityNotFoundException
     */
    private function addToCartData(
        CartDataCollection $data,
        LineItem $subjectContainerProductLineItem,
        SalesChannelContext $salesChannelContext
    ): void {
        $data->set(
            Constants::CART_DATA_KEY_CONTAINER_SALES_CHANNEL_PRODUCT . $subjectContainerProductLineItem->getId(),
            $this->salesChannelProductRepository->findOneById(
                $subjectContainerProductLineItem->getReferencedId(),
                $salesChannelContext
            )
        );
    }

    /**
     * @param LineItem $subjectContainerProductLineItem
     * @param SalesChannelContext $salesChannelContext
     * @param Cart $originalCart
     * @param CartDataCollection $data
     */
    private function enrichSubjectProduct(
        LineItem $subjectContainerProductLineItem,
        SalesChannelContext $salesChannelContext,
        Cart $originalCart,
        CartDataCollection $data
    ): void {

        $this->enrichDeliveryTime(
            $subjectContainerProductLineItem,
            $salesChannelContext,
            $originalCart,
            $data
        );

        $subjectContainerProductLineItem->setLabel('Mein Schoko Mix:' . LineItemAccessor::getMixLabel($subjectContainerProductLineItem));
        $subjectChildProductLineItems = $subjectContainerProductLineItem->getChildren()->filterType(
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );

        if (count($subjectChildProductLineItems) === 0) {
            throw new \RuntimeException(sprintf('Container "%s" has no products',
                $subjectContainerProductLineItem->getLabel()));
        }
    }

    /**
     * @param LineItem $subjectContainerProductLineItem
     * @param SalesChannelContext $salesChannelContext
     * @param Cart $originalCart
     * @param CartDataCollection $data
     */
    private function enrichDeliveryTime(
        LineItem $subjectContainerProductLineItem,
        SalesChannelContext $salesChannelContext,
        Cart $originalCart,
        CartDataCollection $data

    ) {

        $containerProduct = $data->get(Constants::CART_DATA_KEY_CONTAINER_SALES_CHANNEL_PRODUCT . $subjectContainerProductLineItem->getId());
        $containerProductDeliveryTime = $containerProduct->getDeliveryTime();
        if ($containerProductDeliveryTime !== null) {
            $containerProductDeliveryTime = DeliveryTime::createFromEntity($containerProductDeliveryTime);
        }

        $subjectContainerProductLineItem->setRemovable(true)
            ->setStackable(true)
            ->setDeliveryInformation(
                new DeliveryInformation(
                    $containerProduct->getStock(),
                    (float)$containerProduct->getWeight(),
                    (bool)$containerProduct->getShippingFree(),
                    $containerProduct->getRestockTime(),
                    $containerProductDeliveryTime
                )
            )
            ->setQuantityInformation(new QuantityInformation());
    }

    private function handleError(
        Cart $original,
        LineItemCollection $subjectProductLineItems,
        Error $error
    ): void {
        $original->addErrors($error);

        foreach ($subjectProductLineItems as $lineItem) {
            $original->remove($lineItem->getId());
        }
    }

}
