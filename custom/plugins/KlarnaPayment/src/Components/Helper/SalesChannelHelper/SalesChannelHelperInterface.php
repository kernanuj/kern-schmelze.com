<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\SalesChannelHelper;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

interface SalesChannelHelperInterface
{
    public function getSalesChannel(string $salesChannelId, Context $context): SalesChannelEntity;
}
