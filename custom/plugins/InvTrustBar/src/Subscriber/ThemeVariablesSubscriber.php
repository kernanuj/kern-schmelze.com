<?php
// InvTrustBar/src/Subscriber/ThemeVariablesSubscriber.php

namespace InvTrustBar\Subscriber;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Shopware\Storefront\Event\StorefrontRenderEvent;

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
            ThemeCompilerEnrichScssVariablesEvent::class => 'onAddVariables',
            StorefrontRenderEvent::class => 'onStorefrontRender'
        ];
    }

    public function onAddVariables(ThemeCompilerEnrichScssVariablesEvent $event)
    {
        /** @var string $configBackgroundColorField */
        $configBackgroundColorField = $this->systemConfigService->get('InvTrustbar.config.backgroundColor', $event->getSalesChannelId());
        $event->addVariable('inv-trust-bar-background-color', $configBackgroundColorField);

        /** @var string $configTextColorField */
        $configTextColorField = $this->systemConfigService->get('InvTrustbar.config.textColor', $event->getSalesChannelId());
        $event->addVariable('inv-trust-bar-text-color', $configTextColorField);

        /** @var string $configIconWidthField */
        $configIconWidthField = $this->systemConfigService->get('InvTrustbar.config.iconWidth', $event->getSalesChannelId());
        $event->addVariable('inv-trust-bar-icon-width', $configIconWidthField);

        /** @var string $configIconHeightField */
        $configIconHeightField = $this->systemConfigService->get('InvTrustbar.config.iconHeight', $event->getSalesChannelId());
        $event->addVariable('inv-trust-bar-icon-height', $configIconHeightField);


        $configFields = $this->systemConfigService->get('InvTrustbar.config', $event->getSalesChannelId());

        foreach($configFields as $key => $value) {
            // Convert `customVariableName` to `custom-variable-name`
            $variableName = str_replace('_', '-', (new CamelCaseToSnakeCaseNameConverter())->normalize($key));

            $event->addVariable($variableName, $value);
        }
    }

    public function onStorefrontRender(StorefrontRenderEvent $event)
    {
        /** @var bool $InvTrustbarStatus */
        $InvTrustbarStatus = $this->systemConfigService->get('InvTrustBar.config.status');
        $event->setParameter('InvTrustbarStatus', $InvTrustbarStatus);

        /** @var string $InvTrustbarBenefit1 */
        $InvTrustbarBenefit1 = $this->systemConfigService->get('InvTrustBar.config.benefit1');
        $event->setParameter('InvTrustbarBenefit1', $InvTrustbarBenefit1);

        /** @var string $InvTrustbarBenefit2 */
        $InvTrustbarBenefit2 = $this->systemConfigService->get('InvTrustBar.config.benefit2');
        $event->setParameter('InvTrustbarBenefit2', $InvTrustbarBenefit2);

        /** @var string $InvTrustbarBenefit3 */
        $InvTrustbarBenefit3 = $this->systemConfigService->get('InvTrustBar.config.benefit3');
        $event->setParameter('InvTrustbarBenefit3', $InvTrustbarBenefit3);

        /** @var string $InvTrustbarHotline */
        $InvTrustbarHotline = $this->systemConfigService->get('InvTrustBar.config.hotline');
        $event->setParameter('InvTrustbarHotline', $InvTrustbarHotline);

        /** @var string $InvTrustbarHotlineLink */
        $InvTrustbarHotlineLink = $this->systemConfigService->get('InvTrustBar.config.hotlineLink');
        $event->setParameter('InvTrustbarHotlineLink', $InvTrustbarHotlineLink);

        /** @var string $InvTrustbarIcon1 */
        $InvTrustbarIcon1 = $this->systemConfigService->get('InvTrustBar.config.icon1');
        $event->setParameter('InvTrustbarIcon1', $InvTrustbarIcon1);

        /** @var string $InvTrustbarIcon2 */
        $InvTrustbarIcon2 = $this->systemConfigService->get('InvTrustBar.config.icon2');
        $event->setParameter('InvTrustbarIcon2', $InvTrustbarIcon2);

        /** @var string $InvTrustbarIcon3 */
        $InvTrustbarIcon3 = $this->systemConfigService->get('InvTrustBar.config.icon3');
        $event->setParameter('InvTrustbarIcon3', $InvTrustbarIcon3);

    }
}
