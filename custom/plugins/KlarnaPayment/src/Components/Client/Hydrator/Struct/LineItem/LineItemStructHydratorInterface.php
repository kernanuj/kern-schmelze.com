<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\LineItem;

use KlarnaPayment\Components\Client\Struct\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface LineItemStructHydratorInterface
{
    /**
     * @return LineItem[]
     */
    public function hydrate(LineItemCollection $cartLineItems, SalesChannelContext $context): array;
}
