<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Constants;
use InvMixerProduct\Entity\MixEntity as Subject;
use InvMixerProduct\Repository\ProductRepository;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
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
     * @var QuantityPriceCalculator
     */
    private $priceCalculator;

    /**
     * ContainerMixToCartItemConverter constructor.
     * @param ProductLineItemFactory $productLineItemFactory
     * @param ProductRepository $productRepository
     * @param QuantityPriceCalculator $priceCalculator
     */
    public function __construct(
        ProductLineItemFactory $productLineItemFactory,
        ProductRepository $productRepository,
        QuantityPriceCalculator $priceCalculator
    ) {
        $this->productLineItemFactory = $productLineItemFactory;
        $this->productRepository = $productRepository;
        $this->priceCalculator = $priceCalculator;
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
        $lineItem = new LineItem($subject->getId(), LineItem::PRODUCT_LINE_ITEM_TYPE, $containerProduct->getId(), $quantity);
        $lineItem->setRemovable(true);
        $lineItem->setStackable(true);
        $lineItem->setPayloadValue(Constants::KEY_MIX_LABEL_CART_ITEM, $subject->getLabel());

        foreach ($subject->getItems() as $item) {
            $childLineItem = new LineItem(
                $item->getId(),
                LineItem::PRODUCT_LINE_ITEM_TYPE,
                $item->getProductId(),
                $item->getQuantity() *$quantity
            );


            if(false) {
                /**
                 * @todo: without this section the prices would not be calculated.
                 * is it necessary though?
                 */
                $taxRule = new TaxRule(7);
                $quantityPriceDefinition = new QuantityPriceDefinition(
                    $item->getProduct()->getPrice()->first()->getNet(),
                    new TaxRuleCollection([$taxRule]),
                    2
                );
                $price = $this->priceCalculator->calculate(
                    $quantityPriceDefinition,
                    $salesChannelContext
                );
                $childLineItem->setPrice($price);
                $childLineItem->setPriceDefinition($quantityPriceDefinition);
            }

            $childLineItem->setRemovable(false);
            $childLineItem->setStackable(true);

            $lineItem->addChild(
                $childLineItem
            );

        }

        return $lineItem;
    }

}
