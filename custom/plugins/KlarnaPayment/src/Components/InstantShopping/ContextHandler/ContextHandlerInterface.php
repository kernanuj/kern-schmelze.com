<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\ContextHandler;

use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface ContextHandlerInterface
{
    public function createSalesChannelContext(string $newToken, ?string $customerId, ?string $currencyId, ?string $shippingId, SalesChannelContext $context): SalesChannelContext;
}
