<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\DataProvider;

use LogicException;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class UpdateCallbackProvider implements UpdateCallbackProviderInterface
{
    /** @var CartService */
    private $cartService;

    public function __construct(
        CartService $cartService
    ) {
        $this->cartService = $cartService;
    }

    public function isUpdateRequired(Request $request): bool
    {
        if (!$request->get('merchant_data')) {
            return false;
        }

        if ($this->isBasketUpdateRequired($request) === true) {
            return true;
        }

        if ($this->isAddressUpdateRequired($request) === true) {
            return true;
        }

        return false;
    }

    public function updateBasketPositions(Cart $cart, Request $request, SalesChannelContext $context): Cart
    {
        if ($this->isBasketUpdateRequired($request) === false) {
            return $cart;
        }

        $klarnaOrderLines = array_column($request->get('order_lines'), null, 'reference');

        /** @var LineItem $lineItem */
        foreach ($cart->getLineItems() as $lineItem) {
            if ($lineItem->hasPayloadValue('productNumber')) {
                $identifier = $lineItem->getPayloadValue('productNumber');
            } else {
                $identifier = (string) $lineItem->getReferencedId();
            }

            $identifier = mb_strimwidth($identifier, 0, 64);

            if (!array_key_exists($identifier, $klarnaOrderLines)) {
                $cart = $this->cartService->remove($cart, $lineItem->getId(), $context);

                continue;
            }

            $lineItem->setQuantity($klarnaOrderLines[$identifier]['quantity']);
        }

        return $cart;
    }

    public function recalculateShippingCosts(Cart $cart, Request $request, SalesChannelContext $context): Cart
    {
        $customer = $context->getCustomer();

        if (!$customer) {
            throw new LogicException('Missing customer for shipping recalculation in update callback');
        }

        return $this->cartService->recalculate($cart, $context);
    }

    private function isAddressUpdateRequired(Request $request): bool
    {
        if ($request->get('update_context') === 'identification_updated') {
            return true;
        }

        if ($request->get('update_context') === 'session_updated'
            && $request->get('billing_address')
            && $request->get('shipping_address')
            && array_key_exists('country', $request->get('shipping_address'))
            && array_key_exists('postal_code', $request->get('shipping_address'))
            && array_key_exists('city', $request->get('shipping_address'))
            && array_key_exists('country', $request->get('billing_address'))
            && array_key_exists('postal_code', $request->get('billing_address'))
            && array_key_exists('city', $request->get('billing_address'))
        ) {
            return true;
        }

        return false;
    }

    private function isBasketUpdateRequired(Request $request): bool
    {
        return $request->get('update_context') === 'specifications_selected';
    }
}
