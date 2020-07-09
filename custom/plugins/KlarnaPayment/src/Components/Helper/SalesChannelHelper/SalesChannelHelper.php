<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\SalesChannelHelper;

use LogicException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class SalesChannelHelper implements SalesChannelHelperInterface
{
    /** @var EntityRepositoryInterface */
    private $salesChannelRepository;

    public function __construct(EntityRepositoryInterface $salesChannelRepository)
    {
        $this->salesChannelRepository = $salesChannelRepository;
    }

    public function getSalesChannel(string $salesChannelId, Context $context): SalesChannelEntity
    {
        $criteria = new Criteria([$salesChannelId]);
        $criteria->addAssociation('countries');

        /** @var null|SalesChannelEntity $salesChannel */
        $salesChannel = $this->salesChannelRepository->search($criteria, $context)->get($salesChannelId);

        if (null === $salesChannel) {
            throw new LogicException('could not load sales channel via id');
        }

        return $salesChannel;
    }
}
