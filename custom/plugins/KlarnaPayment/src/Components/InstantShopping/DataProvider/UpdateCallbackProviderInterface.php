<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\DataProvider;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

interface UpdateCallbackProviderInterface
{
    public function isUpdateRequired(Request $request): bool;

    public function recalculateShippingCosts(Cart $cart, Request $request, SalesChannelContext $context): Cart;

    public function updateBasketPositions(Cart $cart, Request $request, SalesChannelContext $context): Cart;
}
