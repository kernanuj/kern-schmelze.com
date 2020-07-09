<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\LineItem;

use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection as CartLineItemCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Currency\CurrencyEntity;

interface LineItemStructHydratorInterface
{
    public function hydrate(CartLineItemCollection $lineItems, CurrencyEntity $currency, Context $context): array;
}
