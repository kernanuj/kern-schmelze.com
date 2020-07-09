<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\CartHasher;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class InstantShoppingCartHasher extends CartHasher
{
    protected function getHashData(Cart $cart, SalesChannelContext $context): array
    {
        $hashData = [];

        foreach ($cart->getLineItems() as $item) {
            $detail = [
                'id'       => $item->getReferencedId(),
                'type'     => $item->getType(),
                'quantity' => $item->getQuantity(),
            ];

            if (null !== $item->getPrice()) {
                $detail['price'] = $item->getPrice()->getTotalPrice();
            }

            $hashData[] = $detail;
        }

        $hashData['currency']       = $context->getCurrency()->getId();
        $hashData['shippingMethod'] = $context->getShippingMethod()->getId();

        return $hashData;
    }
}
