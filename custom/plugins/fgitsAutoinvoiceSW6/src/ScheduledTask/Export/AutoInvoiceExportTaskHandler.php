<?php declare(strict_types=1);

namespace Fgits\AutoInvoice\ScheduledTask\Export;

use Fgits\AutoInvoice\Service\Export;
use Fgits\AutoInvoice\Service\FgitsLibrary\SalesChannel;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 */
class AutoInvoiceExportTaskHandler extends ScheduledTaskHandler
{
    /**
     * @var SalesChannel $salesChannel
     */
    private $salesChannel;

    /**
     * @var Export $export
     */
    private $export;

    /**
     * @var SystemConfigService $systemConfigService
     */
    private $systemConfigService;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * AutoInvoiceExportTaskHandler constructor.
     *
     * @param EntityRepositoryInterface $scheduledTaskRepository
     * @param SalesChannel $salesChannel
     * @param Export $export
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        SalesChannel $salesChannel,
        Export $export,
        SystemConfigService $systemConfigService,
        LoggerInterface $logger
    ) {
        parent::__construct($scheduledTaskRepository);

        $this->salesChannel        = $salesChannel;
        $this->export              = $export;
        $this->systemConfigService = $systemConfigService;
        $this->logger              = $logger;
    }

    public static function getHandledMessages(): iterable
    {
        return [AutoInvoiceExportTask::class];
    }

    public function run(): void
    {
        try {
            foreach ($this->salesChannel->fetchAll() as $salesChannelId => $salesChannel) {
                $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config', $salesChannelId);

                if (empty($config['cronjobActive']) || empty($config['exportDirectoryCron']))
                {
                    continue;
                }

                $this->export->export($salesChannel);
            }
        } catch (\Exception $e) {
            $this->logger->info('[#fgits] fgitsAutoinvoiceSW6: ' . __CLASS__ . '::' . __FUNCTION__ . '(): ' . print_r($e->getMessage(), true));
        }
    }
}
