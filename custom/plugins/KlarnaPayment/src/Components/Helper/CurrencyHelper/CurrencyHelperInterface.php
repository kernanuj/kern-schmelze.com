<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\CurrencyHelper;

use Shopware\Core\Framework\Context;

interface CurrencyHelperInterface
{
    public function getCurrencyIdFromIso(string $currencyIso, Context $context): ?string;
}
