<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\ShippingMethodHelper;

use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface ShippingMethodHelperInterface
{
    public function shippingMethodIdExists(?string $shippingMethodId, SalesChannelContext $context): bool;
}
