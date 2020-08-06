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
use Shopware\Core\Content\Product\Cart\ProductGatewayInterface;
use Shopware\Core\Content\Product\SalesChannel\Price\ProductPriceDefinitionBuilderInterface;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class MixProductCartDataCollector
 * @package InvMixerProduct\Service\Checkout
 */
class MixProductCartDataCollector implements CartDataCollectorInterface
{

    use MixProductCartTrait;

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
     * @var ProductPriceDefinitionBuilderInterface
     */
    private $productPriceDefinitionBuilder;

    /**
     * @var ProductGatewayInterface
     */
    private $productGateway;

    /**
     * MixProductCartDataCollector constructor.
     * @param SalesChannelRepositoryInterface $productRepository
     * @param MixEntityRepository $mixRepository
     * @param SalesChannelProductRepository $salesChannelProductRepository
     * @param ProductPriceDefinitionBuilderInterface $productPriceDefinitionBuilder
     * @param ProductGatewayInterface $productGateway
     */
    public function __construct(
        SalesChannelRepositoryInterface $productRepository,
        MixEntityRepository $mixRepository,
        SalesChannelProductRepository $salesChannelProductRepository,
        ProductPriceDefinitionBuilderInterface $productPriceDefinitionBuilder,
        ProductGatewayInterface $productGateway
    ) {
        $this->productRepository = $productRepository;
        $this->mixRepository = $mixRepository;
        $this->salesChannelProductRepository = $salesChannelProductRepository;
        $this->productPriceDefinitionBuilder = $productPriceDefinitionBuilder;
        $this->productGateway = $productGateway;
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

        if (true !== $this->isCartContainsSubjectLineItems($original)) {
            return;
        }

        $subjectContainerProductLineItems = $this->getSubjectLineItemsFromCart($original);

        foreach ($subjectContainerProductLineItems as $subjectContainerProductLineItem) {

            $this->assertIsValidLineItem($subjectContainerProductLineItem);

            $this->enrich(
                $subjectContainerProductLineItem,
                $salesChannelContext,
                $original,
                $data
            );
        }
    }

    /**
     * @param LineItem $subjectContainerProductLineItem
     */
    private function assertIsValidLineItem(LineItem $subjectContainerProductLineItem): void
    {
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
     * @throws EntityNotFoundException
     */
    private function enrich(
        LineItem $subjectContainerProductLineItem,
        SalesChannelContext $salesChannelContext,
        Cart $originalCart,
        CartDataCollection $data
    ): void {

        if (true !== $subjectContainerProductLineItem->isModified()) {
            return;
        }

        $this
            ->enrichQuantityInformation(
                $subjectContainerProductLineItem
            )
            ->enrichDeliveryTime(
                $subjectContainerProductLineItem,
                $salesChannelContext,
                $data
            )->enrichBaseProductCover(
                $subjectContainerProductLineItem,
                $salesChannelContext,
                $data
            )->enrichContainerProductLabel(
                $subjectContainerProductLineItem
            );

    }

    /**
     * @param LineItem $subjectContainerProductLineItem
     * @return $this
     */
    private function enrichContainerProductLabel(LineItem $subjectContainerProductLineItem): self
    {
        if (LineItemAccessor::getMixLabel($subjectContainerProductLineItem)) {
            $separator = ': ';
        } else {
            $separator = '';
        }

        $subjectContainerProductLineItem->setLabel('Meine Schokoladentafel' . $separator . LineItemAccessor::getMixLabel($subjectContainerProductLineItem));

        return $this;
    }

    /**
     * @param LineItem $subjectContainerProductLineItem
     * @param SalesChannelContext $salesChannelContext
     * @param CartDataCollection $data
     * @return $this
     * @throws EntityNotFoundException
     */
    private function enrichBaseProductCover(
        LineItem $subjectContainerProductLineItem,
        SalesChannelContext $salesChannelContext,
        CartDataCollection $data

    ): self {
        $baseProductLineItem = $this->filterBaseProductLineItemFromContainerLineItem($subjectContainerProductLineItem);

        if ($baseProductLineItem->getCover()) {
            return $this;
        }

        $baseProduct = $this->dataGetBaseSalesChannelProduct(
            $data,
            $subjectContainerProductLineItem,
            $salesChannelContext
        );

        if (!$baseProduct->getCover()) {
            return $this;
        }

        $baseProductLineItem->setCover(
            $baseProduct->getCover()->getMedia()
        );

        $subjectContainerProductLineItem->setCover(
            $baseProduct->getCover()->getMedia()
        );

        return $this;
    }

    /**
     * @param CartDataCollection $data
     * @param LineItem $subjectContainerProductLineItem
     * @param SalesChannelContext $context
     * @return SalesChannelProductEntity|null
     * @throws EntityNotFoundException
     */
    private function dataGetBaseSalesChannelProduct(
        CartDataCollection $data,
        LineItem $subjectContainerProductLineItem,
        SalesChannelContext $context
    ): ?SalesChannelProductEntity {
        \assert(LineItemAccessor::isContainsMixContainerProduct($subjectContainerProductLineItem));
        $dataKey = Constants::CART_DATA_KEY_CONTAINER_SALES_CHANNEL_PRODUCT . $subjectContainerProductLineItem->getId();
        $existing = $data->get(
            $dataKey
        );


        if ($existing instanceof SalesChannelProductEntity) {
            return $existing;
        }
        $baseProductLineItem = $this->filterBaseProductLineItemFromContainerLineItem($subjectContainerProductLineItem);
        $baseProduct = $this->productGateway->get(
            [$baseProductLineItem->getReferencedId()],
            $context
        )->first();

        $data->set($dataKey, $baseProduct);


        return $baseProduct;
    }

    /**
     * @param LineItem $subjectContainerProductLineItem
     * @param SalesChannelContext $salesChannelContext
     * @param CartDataCollection $data
     * @return $this
     * @throws EntityNotFoundException
     */
    private function enrichDeliveryTime(
        LineItem $subjectContainerProductLineItem,
        SalesChannelContext $salesChannelContext,
        CartDataCollection $data

    ): self {

        if ($subjectContainerProductLineItem->getDeliveryInformation()) {
            return $this;
        }

        $baseSalesChannelProduct = $this->dataGetBaseSalesChannelProduct(
            $data,
            $subjectContainerProductLineItem,
            $salesChannelContext
        );

        $containerProductDeliveryTime = $baseSalesChannelProduct->getDeliveryTime();
        if ($containerProductDeliveryTime !== null) {
            $containerProductDeliveryTime = DeliveryTime::createFromEntity($containerProductDeliveryTime);
        }

        $subjectContainerProductLineItem->setRemovable(true)
            ->setStackable(true)
            ->setDeliveryInformation(
                new DeliveryInformation(
                    $baseSalesChannelProduct->getStock(),
                    (float)$baseSalesChannelProduct->getWeight(),
                    (bool)$baseSalesChannelProduct->getShippingFree(),
                    $baseSalesChannelProduct->getRestockTime(),
                    $containerProductDeliveryTime
                )
            )
            ->setQuantityInformation(new QuantityInformation());

        return $this;
    }

    /**
     * @param LineItem $subjectContainerProductLineItem
     * @return $this
     */
    private function enrichQuantityInformation(LineItem $subjectContainerProductLineItem): self
    {
        $quantityInformation = new QuantityInformation();

        $quantityInformation->setMinPurchase(
            1
        );

        $quantityInformation->setMaxPurchase(
            99
        );

        $quantityInformation->setPurchaseSteps(
            1
        );

        $subjectContainerProductLineItem->setQuantityInformation($quantityInformation);

        return $this;
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
