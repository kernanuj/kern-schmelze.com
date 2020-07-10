<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Extension\Hydrator\InstantShopping;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Page;

interface DataExtensionHydratorInterface
{
    public function hydrateMerchantUrls(
        string $salesChannelDomain,
        SalesChannelContext $salesChannelContext
    ): array;

    public function hydrateActionUrls(
        string $salesChannelDomainId,
        SalesChannelContext $salesChannelContext
    ): array;

    public function hydrateOrderLines(
        Page $page,
        string $salesChannelDomainEntity,
        SalesChannelContext $salesChannelContext
    ): array;
}
