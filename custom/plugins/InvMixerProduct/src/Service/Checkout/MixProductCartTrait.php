<?php declare(strict_types=1);


namespace InvMixerProduct\Service\Checkout;

use InvMixerProduct\Constants;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;

trait MixProductCartTrait
{
    /**
     * @param LineItem $subjectContainerLineItem
     * @return LineItem
     */
    private function filterBaseProductLineItemFromContainerLineItem(
        LineItem $subjectContainerLineItem
    ): LineItem {

        return $subjectContainerLineItem->getChildren()->filter(
            function (LineItem $child) {
                return (bool)$child->getPayloadValue(Constants::KEY_IS_MIX_BASE_PRODUCT) === true;
            }
        )->first();
    }

    /**
     * @param LineItem $subjectContainerLineItem
     * @return LineItemCollection
     */
    private function filterChildProductLineItemFromContainerLineItem(
        LineItem $subjectContainerLineItem
    ): LineItemCollection {

        return $subjectContainerLineItem->getChildren()->filter(
            function (LineItem $child) {
                return (bool)$child->getPayloadValue(Constants::KEY_IS_MIX_CHILD_PRODUCT) === true;
            }
        );
    }

    /**
     * @param Cart $cart
     * @return bool
     */
    private function isCartContainsSubjectLineItems(Cart $cart): bool
    {
        $subjectContainerProductLineItems = $this->getSubjectLineItemsFromCart($cart);

        if ($subjectContainerProductLineItems->count() === 0) {
            return false;
        }

        return true;
    }

    /**
     * @param Cart $cart
     * @return LineItemCollection
     */
    private function getSubjectLineItemsFromCart(Cart $cart): LineItemCollection
    {
        return $cart->getLineItems()->filterType(
            Constants::LINE_ITEM_TYPE_IDENTIFIER
        );
    }


}
