<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\MerchantDataProvider;

use KlarnaPayment\Components\Extension\SessionDataExtension;
use KlarnaPayment\Components\Struct\ExtraMerchantData;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface MerchantDataProviderInterface
{
    public function getExtraMerchantData(
        SessionDataExtension $sessionData,
        Cart $cart,
        SalesChannelContext $context
    ): ExtraMerchantData;
}
