<?php declare(strict_types=1);

namespace Fgits\AutoInvoice\Service\FgitsLibrary;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskDefinition;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Fabian Golle <fabian@golle-it.de>
 * @version 1.0.0
 */
class ScheduledTask
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * PluginPostActivate constructor.
     *
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger    = $logger;
    }

    /**
     * @param string $taskName
     */
    public function schedule(string $taskName): void
    {
        $scheduledTask = $this->get($taskName);

        if (isset($scheduledTask) && $scheduledTask->getStatus() == ScheduledTaskDefinition::STATUS_QUEUED) {
            $this->setStatus($scheduledTask, ScheduledTaskDefinition::STATUS_SCHEDULED);
        }
    }

    /**
     * @param string $taskName
     *
     * @return ScheduledTaskEntity|null
     */
    private function get(string $taskName): ?ScheduledTaskEntity
    {
        $context = new Context(new SystemSource());

        /** @var EntityRepositoryInterface $scheduledTaskRepository */
        $scheduledTaskRepository = $this->container->get('scheduled_task.repository');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $taskName));

        return $scheduledTaskRepository->search($criteria, $context)->first();
    }

    /**
     * @param ScheduledTaskEntity $scheduledTask
     * @param string $status
     *
     * @return $this
     */
    private function setStatus(ScheduledTaskEntity $scheduledTask, string $status): self
    {
        $context = new Context(new SystemSource());

        /** @var EntityRepositoryInterface $scheduledTaskRepository */
        $scheduledTaskRepository = $this->container->get('scheduled_task.repository');
        $scheduledTaskRepository->update(
            [
                ['id' => $scheduledTask->getId(), 'status' => $status]
            ], $context);

        return $this;
    }
}
