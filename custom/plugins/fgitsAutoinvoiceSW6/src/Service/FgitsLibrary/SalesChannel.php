<?php declare(strict_types=1);

namespace Fgits\AutoInvoice\Service\FgitsLibrary;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 * @version 1.1.0
 */
class SalesChannel
{
    /**
     * @var EntityRepositoryInterface $salesChannelRepository
     */
    private $salesChannelRepository;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * SalesChannel constructor.
     *
     * @param EntityRepositoryInterface $salesChannelRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityRepositoryInterface $salesChannelRepository,
        LoggerInterface $logger
    ) {
        $this->salesChannelRepository = $salesChannelRepository;
        $this->logger                 = $logger;
    }

    /**
     * @return SalesChannelCollection
     */
    public function fetchAll(): SalesChannelCollection
    {
        $context = new Context(new SystemSource());

        return $context->disableCache(function (Context $context) {
            return $this->salesChannelRepository->search(new Criteria(), $context)->getEntities();
        });
    }

    /**
     * @param string $salesChannelId
     *
     * @return SalesChannelEntity|null
     */
    public function get(string $salesChannelId): ?SalesChannelEntity
    {
        $context = new Context(new SystemSource());

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('id', $salesChannelId));

        return $this->salesChannelRepository->search($criteria, $context)->first();
    }
}
