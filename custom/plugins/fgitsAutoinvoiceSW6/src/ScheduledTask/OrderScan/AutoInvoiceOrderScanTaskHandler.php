<?php declare(strict_types=1);

namespace Fgits\AutoInvoice\ScheduledTask\OrderScan;

use Fgits\AutoInvoice\Service\FgitsLibrary\SalesChannel;
use Fgits\AutoInvoice\Service\OrderProcessor;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 */
class AutoInvoiceOrderScanTaskHandler extends ScheduledTaskHandler
{
    /**
     * @var EntityRepositoryInterface $orderRepository
     */
    private $orderRepository;

    /**
     * @var SalesChannel $salesChannel
     */
    private $salesChannel;

    /**
     * @var OrderProcessor $orderProcessor
     */
    private $orderProcessor;

    /**
     * @var SystemConfigService $systemConfigService
     */
    private $systemConfigService;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * AutoInvoiceOrderScanTaskHandler constructor.
     *
     * @param EntityRepositoryInterface $scheduledTaskRepository
     * @param EntityRepositoryInterface $orderRepository
     * @param SalesChannel $salesChannel
     * @param OrderProcessor $orderProcessor
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        EntityRepositoryInterface $orderRepository,
        SalesChannel $salesChannel,
        OrderProcessor $orderProcessor,
        SystemConfigService $systemConfigService,
        LoggerInterface $logger
    ) {
        parent::__construct($scheduledTaskRepository);

        $this->orderRepository     = $orderRepository;
        $this->salesChannel        = $salesChannel;
        $this->orderProcessor      = $orderProcessor;
        $this->systemConfigService = $systemConfigService;
        $this->logger              = $logger;
    }

    public static function getHandledMessages(): iterable
    {
        return [AutoInvoiceOrderScanTask::class];
    }

    public function run(): void
    {
        try {
            foreach ($this->salesChannel->fetchAll() as $salesChannelId => $salesChannel) {
                $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config', $salesChannelId);

                if (empty($config['cronjobActive']))
                {
                    continue;
                }

                foreach ($this->getUnprocessedOrders($salesChannelId) as $order)
                {
                    $this->orderProcessor->processOrder($order);

                    $this->orderRepository->upsert([[
                        'id' => $order->getId(),
                        'customFields' => [
                            'fgits_autoinvoice_cron_date' => date('Y-m-d H:i:s')
                        ]
                    ]], new Context(new SystemSource()));
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('[#fgits] fgitsAutoinvoiceSW6: ' . __CLASS__ . '::' . __FUNCTION__ . '(): ' . print_r($e->getMessage(), true));
        }
    }

    /**
     * @param string $salesChannelId
     *
     * @return OrderCollection
     */
    private function getUnprocessedOrders(string $salesChannelId): OrderCollection
    {
        $context = new Context(new SystemSource());

        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('createdAt'));
        $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannelId));
        $criteria->addFilter(
            new MultiFilter(
                MultiFilter::CONNECTION_OR,
                [
                    new EqualsFilter('customFields.fgits_autoinvoice_processed', null),
                    new EqualsFilter('customFields.fgits_autoinvoice_processed', false)
                ]
            )
        );
        $criteria->addFilter(
            new MultiFilter(
                MultiFilter::CONNECTION_OR,
                [
                    new EqualsFilter('customFields.fgits_autoinvoice_cron_date', null),
                    new RangeFilter('customFields.fgits_autoinvoice_cron_date', ['lt' => date('Y-m-d H:i:s', (time() - 86400))])
                ]
            )
        );

        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config', $salesChannelId);

        if (isset($config['processOrdersAfter'])) {
            $criteria->addFilter(new RangeFilter('createdAt', ['gte' => $config['processOrdersAfter']]));
        }

        if (isset($config['cronjobOrderLimit']) && $config['cronjobOrderLimit'] < 1000) {
            $criteria->setLimit($config['cronjobOrderLimit']);
        } else {
            $criteria->setLimit(1000);
        }

        /** @var OrderCollection $orders */
        $orders = $this->orderRepository->search($criteria, $context)->getEntities();

        return $orders;
    }
}
