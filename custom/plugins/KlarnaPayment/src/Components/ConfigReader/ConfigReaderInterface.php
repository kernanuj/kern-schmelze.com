<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\ConfigReader;

use KlarnaPayment\Components\Struct\Configuration;

interface ConfigReaderInterface
{
    public const SYSTEM_CONFIG_DOMAIN = 'KlarnaPayment.settings.';

    public function read(string $salesChannelId = null, bool $inherit = true): Configuration;
}
