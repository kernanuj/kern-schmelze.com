<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\ShippingOptions;

use KlarnaPayment\Components\Client\Struct\ShippingOption;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface ShippingOptionsStructHydratorInterface
{
    /**
     * @return ShippingOption[]
     */
    public function hydrate(Cart $cart, SalesChannelContext $context): array;
}
