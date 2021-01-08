<?php

namespace Fgits\AutoInvoice\Service;

use Exception;
use Fgits\AutoInvoice\Service\CustomFields\OrderCustomFields;
use Fgits\AutoInvoice\Service\DB\Order;
use Fgits\AutoInvoice\Service\FgitsLibrary\SalesChannel;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Shopware\Core\Kernel;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class Export
{
    /**
     * @var Order $order
     */
    private $order;

    /**
     * @var OrderCustomFields $orderCustomFields
     */
    private $orderCustomFields;

    /**
     * @var SalesChannel $salesChannel
     */
    private $salesChannel;

    /**
     * @var Document $document
     */
    private $document;

    /**
     * @var Kernel $kernel
     */
    private $kernel;

    /**
     * @var SystemConfigService $systemConfigService
     */
    private $systemConfigService;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * Export constructor.
     *
     * @param Order $order
     * @param OrderCustomFields $orderCustomFields
     * @param SalesChannel $salesChannel
     * @param Document $document
     * @param Kernel $kernel
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     */
    public function __construct(
        Order $order,
        OrderCustomFields $orderCustomFields,
        SalesChannel $salesChannel,
        Document $document,
        Kernel $kernel,
        SystemConfigService $systemConfigService,
        LoggerInterface $logger
    ) {
        $this->order               = $order;
        $this->orderCustomFields   = $orderCustomFields;
        $this->salesChannel        = $salesChannel;
        $this->document            = $document;
        $this->kernel              = $kernel;
        $this->systemConfigService = $systemConfigService;
        $this->logger              = $logger;
    }

    /**
     * @param SalesChannelEntity|null $salesChannel
     */
    public function export(?SalesChannelEntity $salesChannel = null) {
        if (isset($salesChannel)) {
            $this->exportInvoices($salesChannel);
        } else {
            foreach ($this->salesChannel->fetchAll() as $salesChannel) {
                $this->exportInvoices($salesChannel);
            }
        }
    }

    /**
     * @param SalesChannelEntity $salesChannel
     */
    private function exportInvoices(SalesChannelEntity $salesChannel) {
        $orders = $this->order->getNotExportedOrders($salesChannel);

        foreach ($orders as $order) {
            try {
                $exportDirectory = $this->getExportDirectory($salesChannel, 'invoices');

                $invoice = $this->document->getInvoice($order);

                $this->saveToFile(sprintf('%s%s', $exportDirectory, $invoice['fileName']), $invoice['content']);
            } catch (InvalidArgumentException $e) {
                $this->logger->error(sprintf('[#fgits]: %s::%s(): %s', __CLASS__, __FUNCTION__, $e->getMessage()));
            } catch (Exception $e) {}

            try {
                $exportDirectory = $this->getExportDirectory($salesChannel, 'delivery_notes');

                $deliveryNote = $this->document->getDeliveryNote($order);

                $this->saveToFile(sprintf('%s%s', $exportDirectory, $deliveryNote['fileName']), $deliveryNote['content']);
            } catch (InvalidArgumentException $e) {
                $this->logger->error(sprintf('[#fgits]: %s::%s(): %s', __CLASS__, __FUNCTION__, $e->getMessage()));
            } catch (Exception $e) {}

            $this->orderCustomFields->setExported($order);
        }
    }

    /**
     * @param SalesChannelEntity $salesChannel
     * @param string $folder
     *
     * @return string
     */
    private function getExportDirectory(SalesChannelEntity $salesChannel, string $folder): string
    {
        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config', $salesChannel->getId());

        if (!($exportDirectory = trim($config['exportDirectory']))) {
            throw new InvalidArgumentException('exportDirectory must not be empty.');
        }

        $exportDir = sprintf(
            '%s%s/%s (%s)/%s',
            $this->kernel->getProjectDir(),
            str_replace('//', '/', $exportDirectory),
            $salesChannel->getName(),
            $salesChannel->getId(),
            $folder
        );

        if ($exportDir[strlen($exportDir) - 1] !== '/') {
            $exportDir .= '/';
        }

        if (!@mkdir($exportDir, 0777, true) && !is_dir($exportDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $exportDir));
        }

        return $exportDir;
    }

    /**
     * @param string $filename
     * @param string $data
     */
    private function saveToFile(string $filename, string $data): void
    {
        $fp = @fopen($filename, 'w+');

        @fwrite($fp, $data);

        @fclose($fp);
    }
}
