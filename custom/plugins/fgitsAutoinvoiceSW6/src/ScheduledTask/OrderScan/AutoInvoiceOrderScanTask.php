<?php declare(strict_types=1);

namespace Fgits\AutoInvoice\ScheduledTask\OrderScan;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 */
class AutoInvoiceOrderScanTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'fgits_autoinvoice.auto_invoice_order_scan';
    }

    /**
     * @inheritDoc
     */
    public static function getDefaultInterval(): int
    {
        return 600;
    }
}
