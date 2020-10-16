<?php

namespace Tanmar\NgInfiniteScrolling\Storefront;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Tanmar\NgInfiniteScrolling\Service\ConfigService;
use Tanmar\NgInfiniteScrolling\Components\Config;
use Tanmar\NgInfiniteScrolling\Components\TanmarNgInfiniteScrollingData;
use Shopware\Core\Framework\Event\NestedEvent;

class BaseSubscriber {

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $extensionName = "TanmarNgInfiniteScrolling";

    public function __construct(ConfigService $configService) {
        $this->config = $configService->getConfig();
    }

    /**
     * 
     * @return Config
     */
    protected function getConfig(): Config {
        return $this->config;
    }

    /**
     * Loads an extension from the storefront context if it exists
     * or creates a new extension, with initialized variables.
     * The name of the extension is defined as a class variable.
     * 
     * @param NestedEvent $event
     * @return TanmarNgInfiniteScrollingData
     */
    protected function getExtension(NestedEvent $event): TanmarNgInfiniteScrollingData {
        try {
            $extension = $event->getContext()->getExtension($this->extensionName);
            if (is_null($extension)) {
                $extension = $this->initializeData();
            }
        } catch (\Exception $e) {
            $extension = $this->initializeData();
        }
        return $extension;
    }

    /**
     * 
     * Adds an extension to the storefront context.
     * 
     * @param NestedEvent $event
     * @param TanmarNgInfiniteScrollingData $extension
     * @return bool
     */
    protected function addExtension(NestedEvent $event, TanmarNgInfiniteScrollingData $extension): bool {
        try {
            $event->getContext()->addExtension($this->extensionName, $extension);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Values active, retargetingActive and conversionId are initially set to config values
     * @param string $salesChannelId
     * @return TanmarNgInfiniteScrollingData
     */
    private function initializeData(): TanmarNgInfiniteScrollingData {
        $productData = new TanmarNgInfiniteScrollingData();
        $config = $this->getConfig();
        if (!is_null($config)) {
            $productData->assign([
                'active' => $config->isActive(),
                'pages' => $config->getPages(),
            ]);
        }
        return $productData;
    }

}
