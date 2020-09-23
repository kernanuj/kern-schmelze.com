<?php declare(strict_types=1);

namespace TrustedShops\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class ProductReviewsTask extends ScheduledTask
{

    public static function getTaskName(): string
    {
        return 'trustedshops.productreviews';
    }

    public static function getDefaultInterval(): int
    {
        return 86400; // 24 hours
    }

}