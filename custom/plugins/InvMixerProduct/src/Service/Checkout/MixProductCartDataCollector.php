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

            $this->gatherCartData(
                $data,
                $subjectContainerProductLineItem,
                $salesChannelContext
            );

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
     * @param CartDataCollection $data
     * @param LineItem $subjectContainerProductLineItem
     * @param SalesChannelContext $salesChannelContext
     * @throws EntityNotFoundException
     */
    private function gatherCartData(
        CartDataCollection $data,
        LineItem $subjectContainerProductLineItem,
        SalesChannelContext $salesChannelContext
    ): void {


        if(is_null($this->dataGetBaseSalesChannelProduct(
            $data,
            $subjectContainerProductLineItem->getId()
        ))) {

            $baseProductLineItem = $this->filterBaseProductLineItemFromContainerLineItem($subjectContainerProductLineItem);

            $this->dataSetBaseSalesChannelProduct(
                $data,
                $subjectContainerProductLineItem->getId(),
                $this->salesChannelProductRepository->findOneById(
                    $baseProductLineItem->getReferencedId(),
                    $salesChannelContext
                )
            );
        }
    }

    /**
     * @param CartDataCollection $data
     * @param string $identifier
     * @param SalesChannelProductEntity $productEntity
     * @return $this
     */
    private function dataSetBaseSalesChannelProduct(
        CartDataCollection $data,
        string $identifier,
        SalesChannelProductEntity $productEntity
    ): self {
        $data->set(
            Constants::CART_DATA_KEY_CONTAINER_SALES_CHANNEL_PRODUCT . $identifier,
            $productEntity
        );

        return $this;
    }

    /**
     * @param LineItem $subjectContainerProductLineItem
     * @param SalesChannelContext $salesChannelContext
     * @param Cart $originalCart
     * @param CartDataCollection $data
     */
    private function enrich(
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
        /*
                $priceDefinition = $this->productPriceDefinitionBuilder->build(
                    $this->dataGetContainerSalesChannelProduct(
                        $data,
                        $subjectContainerProductLineItem->getId()
                    ),
                    $salesChannelContext, $subjectContainerProductLineItem->getQuantity()
                );

                $subjectContainerProductLineItem->setPriceDefinition($priceDefinition->getQuantityPrice());

        */
        $this->enrichContainerProductLabel($subjectContainerProductLineItem);
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

        $baseSalesChannelProduct = $this->dataGetBaseSalesChannelProduct(
            $data,
            $subjectContainerProductLineItem->getId()
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
    }

    /**
     * @param CartDataCollection $data
     * @param string $identifier
     * @return SalesChannelProductEntity|null
     */
    private function dataGetBaseSalesChannelProduct(
        CartDataCollection $data,
        string $identifier
    ): ?SalesChannelProductEntity {
        return $data->get(
            Constants::CART_DATA_KEY_CONTAINER_SALES_CHANNEL_PRODUCT . $identifier
        );
    }

    /**
     * @param LineItem $subjectContainerProductLineItem
     */
    private function enrichContainerProductLabel(LineItem $subjectContainerProductLineItem): void
    {
        $subjectContainerProductLineItem->setLabel('Mein Schoko Mix:' . LineItemAccessor::getMixLabel($subjectContainerProductLineItem));
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
