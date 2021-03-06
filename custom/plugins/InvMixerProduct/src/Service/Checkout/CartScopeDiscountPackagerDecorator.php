<?php declare(strict_types=1);

namespace InvMixerProduct\Service\Checkout;

use InvMixerProduct\Constants;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemQuantity;
use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemQuantityCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;
use Shopware\Core\Checkout\Cart\Rule\LineItemScope;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountLineItem;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackage;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackager;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CartScopeDiscountPackagerDecorator extends DiscountPackager
{
    /**
     * @var DiscountPackager
     */
    private $originalPackager;

    public function __construct(DiscountPackager $originalPackager)
    {
        $this->originalPackager = $originalPackager;
    }

    /**
     * {@inheritdoc}
     */
    public function getResultContext(): string
    {
        return $this->originalPackager->getResultContext();
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingItems(DiscountLineItem $discount, Cart $cart, SalesChannelContext $context): DiscountPackageCollection
    {
        $originalDiscountPackage = $this->originalPackager->getMatchingItems($discount, $cart, $context);
        $customProductLineItems = $cart
            ->getLineItems()
            ->filterType(Constants::LINE_ITEM_TYPE_IDENTIFIER);

        $items = [];
        foreach ($customProductLineItems as $lineItem) {
            $productLineItem = $lineItem->getChildren()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE)->first();
            if ($productLineItem === null) {
                continue;
            }

            if (!$this->isRulesFilterValid($productLineItem, $discount->getPriceDefinition(), $context)) {
                continue;
            }

            $lineItem->setStackable(true);

            $items[] = new LineItemQuantity($lineItem->getId(), $lineItem->getQuantity());
        }

        if ($items !== []) {
            $originalDiscountPackage->add(new DiscountPackage(new LineItemQuantityCollection($items)));
        }

        return $originalDiscountPackage;
    }

    /**
     * {@inheritdoc}
     */
    public function getDecorated(): DiscountPackager
    {
        return $this->originalPackager;
    }

    private function isRulesFilterValid(LineItem $item, PriceDefinitionInterface $priceDefinition, SalesChannelContext $context): bool
    {
        if (!\method_exists($priceDefinition, 'getFilter')) {
            return true;
        }

        $filter = $priceDefinition->getFilter();
        if (!$filter instanceof Rule) {
            return true;
        }

        $scope = new LineItemScope($item, $context);

        return $filter->match($scope);
    }
}
