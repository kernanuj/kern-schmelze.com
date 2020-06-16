<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Entity\MixEntity as Subject;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\Cart\ProductLineItemFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 *
 * This is just a dummy class that will just convert to a single line item;
 * should be replaced by an actual implementation that will add a single bundle product.
 *
 * Interface MixToCartItemConverterInterface
 * @package InvMixerProduct\Service
 */
class DummyMixToCartItemConverter implements MixToCartItemConverterInterface
{

    /**
     * @var ProductLineItemFactory
     */
    private $productLineItemFactory;

    /**
     * DummyMixToCartItemConverter constructor.
     * @param ProductLineItemFactory $productLineItemFactory
     */
    public function __construct(
        ProductLineItemFactory $productLineItemFactory
    ) {
        $this->productLineItemFactory = $productLineItemFactory;
    }

    /**
     * @param Subject $subject
     * @param SalesChannelContext $salesChannelContext
     * @return LineItem
     */
    public function toCartItem(
        Subject $subject,
        SalesChannelContext $salesChannelContext
    ): LineItem {

        $lineItem = null;
        foreach ($subject->getItems() as $item) {
            $lineItem = $this->productLineItemFactory->create($item->getProductId(),
                ['quantity' => $item->getQuantity()]);
        }

        return $lineItem;
    }

}