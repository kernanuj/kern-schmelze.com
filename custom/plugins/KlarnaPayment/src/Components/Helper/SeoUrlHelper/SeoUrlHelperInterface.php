<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\SeoUrlHelper;

use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface SeoUrlHelperInterface
{
    public function getSeoUrlFromDomainId(
        array $urlParameters,
        string $salesChannelDomainId,
        SalesChannelContext $salesChannelContext,
        string $target = 'frontend.navigation.page'
    ): string;

    public function getSeoUrl(
        array $urlParameters,
        SalesChannelDomainEntity $salesChannelDomain,
        string $target = 'frontend.navigation.page'
    ): string;
}
