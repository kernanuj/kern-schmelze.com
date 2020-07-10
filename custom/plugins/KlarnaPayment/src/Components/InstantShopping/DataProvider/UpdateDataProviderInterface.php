<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\DataProvider;

use KlarnaPayment\Components\Client\Struct\Attachment;
use KlarnaPayment\Components\Struct\ExtraMerchantData;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface UpdateDataProviderInterface
{
    public function buildAttachment(ExtraMerchantData $extraMerchantData): ?Attachment;

    public function createInstantShoppingCart(string $productId, int $productQuantity, SalesChannelContext $context): ?Cart;

    public function getInstantShoppingCart(SalesChannelContext $context): ?Cart;
}
