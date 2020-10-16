<?php

declare(strict_types=1);

namespace Tanmar\NgInfiniteScrolling\Components;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class Config {

    private $pluginName = 'TanmarNgInfiniteScrolling';
    private $path;
    private $active;
    private $pages;

    public function __construct(SystemConfigService $systemConfigService, string $salesChannelId) {
        $this->path = $this->pluginName . '.config.';
        $this->active = !is_null($systemConfigService->get($this->path . 'active', $salesChannelId)) ? $systemConfigService->get($this->path . 'active', $salesChannelId) : false;
        $this->pages = !is_null($systemConfigService->get($this->path . 'pages', $salesChannelId)) ? $systemConfigService->get($this->path . 'pages', $salesChannelId) : false;
    }

    public function isActive(): bool {
        return $this->active;
    }

    public function getPages(): int {
        return $this->pages;
    }
}
