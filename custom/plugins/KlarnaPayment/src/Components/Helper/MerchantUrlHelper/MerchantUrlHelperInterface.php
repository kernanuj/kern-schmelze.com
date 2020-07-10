<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\MerchantUrlHelper;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Symfony\Component\HttpFoundation\Request;

interface MerchantUrlHelperInterface
{
    public function getMerchantUrls(SalesChannelDomainEntity $salesChannelDomain): array;

    public function getSalesChannelDomainFromRequest(Request $request, Context $context): SalesChannelDomainEntity;
}
