<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\DataAbstractionLayer\Entity\ButtonKey;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;

class ButtonKeyEntity extends Entity
{
    use EntityIdTrait;

    /** @var string */
    protected $buttonKey;

    /** @var string */
    protected $salesChannelDomainId;

    /** @var null|SalesChannelDomainEntity */
    protected $salesChannelDomain;

    public function getButtonKey(): string
    {
        return $this->buttonKey;
    }

    public function setButtonKey(string $buttonKey): void
    {
        $this->buttonKey = $buttonKey;
    }

    public function getSalesChannelDomainId(): string
    {
        return $this->salesChannelDomainId;
    }

    public function setSalesChannelDomainId(string $salesChannelDomainId): void
    {
        $this->salesChannelDomainId = $salesChannelDomainId;
    }

    public function getSalesChannelDomain(): ?SalesChannelDomainEntity
    {
        return $this->salesChannelDomain;
    }

    public function setSalesChannelDomain(SalesChannelDomainEntity $salesChannelDomain): void
    {
        $this->salesChannelDomain = $salesChannelDomain;
    }
}
