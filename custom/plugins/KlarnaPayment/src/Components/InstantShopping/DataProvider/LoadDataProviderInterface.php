<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\DataProvider;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Page;

interface LoadDataProviderInterface
{
    public function registerInstantShopping(Page $page, SalesChannelContext $salesChannelContext): void;
}
