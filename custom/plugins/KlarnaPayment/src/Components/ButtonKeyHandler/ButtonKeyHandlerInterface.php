<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\ButtonKeyHandler;

use KlarnaPayment\Components\DataAbstractionLayer\Entity\ButtonKey\ButtonKeyEntity;
use Shopware\Core\Framework\Context;

interface ButtonKeyHandlerInterface
{
    public function getButtonKey(string $salesChannelDomainId, Context $context): ?ButtonKeyEntity;

    public function getOrCreateButtonKey(string $salesChannelDomainId, Context $context): ?ButtonKeyEntity;

    public function createButtonKey(string $salesChannelDomainId, Context $context): void;

    public function deleteButtonKey(string $salesChannelDomainId, Context $context): void;

    public function deleteButtonKeysBySalesChannelId(string $salesChannelId, Context $context): void;

    public function createButtonKeysBySalesChannelId(string $salesChannelId, Context $context): void;

    public function deleteButtonKeysBySalesChannelDomainId(string $salesChannelDomainId, Context $context): void;

    public function createButtonKeysBySalesChannelDomainId(string $salesChannelDomainId, Context $context): void;

    public function createButtonKeysForAllDomains(Context $context): void;
}
