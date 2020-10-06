<?php
// InvTrustBar/src/Subscriber/ThemeVariablesSubscriber.php

namespace InvTrustBar\Subscriber;

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
            ThemeCompilerEnrichScssVariablesEvent::class => 'onAddVariables',
            StorefrontRenderEvent::class => 'onStorefrontRender'
        ];
    }

    public function onAddVariables(ThemeCompilerEnrichScssVariablesEvent $event)
    {
        /** @var string $configBackgroundColorField */
        $configBackgroundColorField = $this->systemConfigService->get('InvTrustBar.config.backgroundColor', $event->getSalesChannelId());
        if ($configBackgroundColorField) {
            $event->addVariable('inv-trust-bar-background-color', $configBackgroundColorField);
        }

        /** @var string $configTextColorField */
        $configTextColorField = $this->systemConfigService->get('InvTrustBar.config.textColor', $event->getSalesChannelId());
        if ($configTextColorField) {
            $event->addVariable('inv-trust-bar-text-color', $configTextColorField);
        }

        /** @var string $configIconWidthField */
        $configIconWidthField = $this->systemConfigService->get('InvTrustBar.config.iconWidth', $event->getSalesChannelId());
        if ($configIconWidthField) {
            $event->addVariable('inv-trust-bar-icon-width', $configIconWidthField);
        }

        /** @var string $configIconHeightField */
        $configIconHeightField = $this->systemConfigService->get('InvTrustBar.config.iconHeight', $event->getSalesChannelId());
        if ($configIconHeightField) {
            $event->addVariable('inv-trust-bar-icon-height', $configIconHeightField);
        }

        /** @var string $configFontSizeField */
        $configFontSizeField = $this->systemConfigService->get('InvTrustBar.config.fontSize', $event->getSalesChannelId());
        if ($configFontSizeField) {
            $event->addVariable('inv-trust-bar-font-size', $configFontSizeField);
        }

        /** @var string $configLineHeightField */
        $configLineHeightField = $this->systemConfigService->get('InvTrustBar.config.lineHeight', $event->getSalesChannelId());
        if ($configLineHeightField) {
            $event->addVariable('inv-trust-bar-line-height', $configLineHeightField);
        }

        /** @var string $configIconWidthTrustedshopsField */
        $configIconWidthTrustedshopsField = $this->systemConfigService->get('InvTrustBar.config.iconWidthTrustedshops', $event->getSalesChannelId());
        if ($configIconWidthTrustedshopsField) {
            $event->addVariable('inv-trust-bar-icon-width-trustedshops', $configIconWidthTrustedshopsField);
        }

        /** @var string $configIconHeightTrustedshopsField */
        $configIconHeightTrustedshopsField = $this->systemConfigService->get('InvTrustBar.config.iconHeightTrustedshops', $event->getSalesChannelId());
        if ($configIconHeightTrustedshopsField) {
            $event->addVariable('inv-trust-bar-icon-height-trustedshops', $configIconHeightTrustedshopsField);
        }

        /*$configFields = $this->systemConfigService->get('InvTrustBar.config', $event->getSalesChannelId());

        foreach($configFields as $key => $value) {
            // Convert `customVariableName` to `custom-variable-name`
            $variableName = str_replace('_', '-', (new CamelCaseToSnakeCaseNameConverter())->normalize($key));

            if ($value) {
                $event->addVariable($variableName, $value);
            }
        }*/
    }

    public function onStorefrontRender(StorefrontRenderEvent $event)
    {
        /** @var bool $InvTrustBarStatus */
        $InvTrustBarStatus = $this->systemConfigService->get('InvTrustBar.config.status');
        $event->setParameter('InvTrustBarStatus', $InvTrustBarStatus);

        /** @var string $InvTrustBarBenefit1 */
        $InvTrustBarBenefit1 = $this->systemConfigService->get('InvTrustBar.config.benefit1');
        $event->setParameter('InvTrustBarBenefit1', $InvTrustBarBenefit1);

        /** @var string $InvTrustBarBenefit2 */
        $InvTrustBarBenefit2 = $this->systemConfigService->get('InvTrustBar.config.benefit2');
        $event->setParameter('InvTrustBarBenefit2', $InvTrustBarBenefit2);

        /** @var string $InvTrustBarBenefit3 */
        $InvTrustBarBenefit3 = $this->systemConfigService->get('InvTrustBar.config.benefit3');
        $event->setParameter('InvTrustBarBenefit3', $InvTrustBarBenefit3);

        /** @var string $InvTrustBarHotline */
        $InvTrustBarHotline = $this->systemConfigService->get('InvTrustBar.config.hotline');
        $event->setParameter('InvTrustBarHotline', $InvTrustBarHotline);

        /** @var string $InvTrustBarHotlineLink */
        $InvTrustBarHotlineLink = $this->systemConfigService->get('InvTrustBar.config.hotlineLink');
        $event->setParameter('InvTrustBarHotlineLink', $InvTrustBarHotlineLink);

        /** @var string $InvTrustBarIcon1 */
        $InvTrustBarIcon1 = $this->systemConfigService->get('InvTrustBar.config.icon1');
        $event->setParameter('InvTrustBarIcon1', $InvTrustBarIcon1);

        /** @var string $InvTrustBarIcon2 */
        $InvTrustBarIcon2 = $this->systemConfigService->get('InvTrustBar.config.icon2');
        $event->setParameter('InvTrustBarIcon2', $InvTrustBarIcon2);

        /** @var string $InvTrustBarIcon3 */
        $InvTrustBarIcon3 = $this->systemConfigService->get('InvTrustBar.config.icon3');
        $event->setParameter('InvTrustBarIcon3', $InvTrustBarIcon3);

    }
}
