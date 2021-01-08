<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\ContextHandler;

use KlarnaPayment\Installer\Modules\PaymentMethodInstaller;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ContextHandler implements ContextHandlerInterface
{
    /** @var SalesChannelContextFactory */
    private $salesChannelContextFactory;

    public function __construct(
        SalesChannelContextFactory $salesChannelContextFactory
    ) {
        $this->salesChannelContextFactory = $salesChannelContextFactory;
    }

    public function createSalesChannelContext(string $newToken, ?string $customerId, ?string $currencyId, ?string $shippingId, SalesChannelContext $context): SalesChannelContext
    {
        $salesChannelContext = $this->salesChannelContextFactory->create(
            $newToken,
            $context->getSalesChannel()->getId(),
            [
                SalesChannelContextService::CUSTOMER_ID        => $customerId,
                SalesChannelContextService::CURRENCY_ID        => $currencyId ?? $context->getCurrency()->getId(),
                SalesChannelContextService::PAYMENT_METHOD_ID  => PaymentMethodInstaller::KLARNA_INSTANT_SHOPPING,
                SalesChannelContextService::SHIPPING_METHOD_ID => $shippingId ?? $context->getShippingMethod()->getId(),
            ]
        );

        $salesChannelContext->setRuleIds($context->getRuleIds());

        return $salesChannelContext;
    }
}
