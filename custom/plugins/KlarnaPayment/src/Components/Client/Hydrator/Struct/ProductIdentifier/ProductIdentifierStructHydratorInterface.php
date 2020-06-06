<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\ProductIdentifier;

use KlarnaPayment\Components\Client\Struct\ProductIdentifier;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface ProductIdentifierStructHydratorInterface
{
    public function hydrate(ProductEntity $product, SalesChannelContext $context): ?ProductIdentifier;
}
