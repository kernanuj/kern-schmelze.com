<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Request\CreateButtonKey;

use KlarnaPayment\Components\Client\Request\CreateButtonKeyRequest;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;

interface CreateButtonKeyRequestHydratorInterface
{
    public function hydrate(SalesChannelDomainEntity $salesChannelDomain, Context $context): CreateButtonKeyRequest;
}
