<?php
// InvPromoBarPro/src/Subscriber/ThemeVariablesSubscriber.php

namespace InvPromoBarPro\Subscriber;

use Shopware\Storefront\Event\ThemeCompilerEnrichScssVariablesEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Storefront\Event\StorefrontRenderEvent;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class ThemeVariablesSubscriber implements EventSubscriberInterface
{
    /**
    * @var SystemConfigService
    */
    protected $systemConfigService;

    // add the `SystemConfigService` to your constructor
    public function __construct(SystemConfigService $config)
    {
        $this->systemConfigService = $config;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ThemeCompilerEnrichScssVariablesEvent::class => 'onAddVariables'
        ];
    }

    public function onAddVariables(ThemeCompilerEnrichScssVariablesEvent $event)
    {
        /** @var string $configBackgroundColorField */
        $configBackgroundColorField = $this->systemConfigService->get('InvPromoBarPro.config.backgroundColor', $event->getSalesChannelId());
        if ($configBackgroundColorField) {
            $event->addVariable('inv-promo-bar-pro-background-color', $configBackgroundColorField);
        }

        /** @var string $configTextColorField */
        $configTextColorField = $this->systemConfigService->get('InvPromoBarPro.config.textColor', $event->getSalesChannelId());
        if ($configTextColorField) {
            $event->addVariable('inv-promo-bar-pro-text-color', $configTextColorField);
        }

        /** @var string $configFontSizeField */
        $configFontSizeField = $this->systemConfigService->get('InvPromoBarPro.config.fontSize', $event->getSalesChannelId());
        if ($configFontSizeField) {
            $event->addVariable('inv-promo-bar-pro-font-size', $configFontSizeField);
        }

        /** @var string $configLineHeightField */
        $configLineHeightField = $this->systemConfigService->get('InvPromoBarPro.config.lineHeight', $event->getSalesChannelId());
        if ($configLineHeightField) {
            $event->addVariable('inv-promo-bar-pro-line-height', $configLineHeightField);
        }
    }
}
