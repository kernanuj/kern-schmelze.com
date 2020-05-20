<?php
// InvPressBar/src/Subscriber/ThemeVariablesSubscriber.php

namespace InvPressBar\Subscriber;

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
        $configFields = $this->systemConfigService->get('InvPressBar.config', $event->getSalesChannelId());

        foreach($configFields as $key => $value) {
            // Convert `customVariableName` to `custom-variable-name`
            $variableName = str_replace('_', '-', (new CamelCaseToSnakeCaseNameConverter())->normalize($key));

            $event->addVariable($variableName, $value);
        }
    }

    public function onStorefrontRender(StorefrontRenderEvent $event)
    {
        /** @var bool $InvPressBarStatus */
        $InvPressBarStatus = $this->systemConfigService->get('InvPressBar.config.status');
        $event->setParameter('InvPressBarStatus', $InvPressBarStatus);

        /** @var bool $InvPressBarDisplayPosition */
        $InvPressBarDisplayPosition = $this->systemConfigService->get('InvPressBar.config.displayPosition');
        $event->setParameter('InvPressBarDisplayPosition', $InvPressBarDisplayPosition);

        /** @var bool $InvPressBarHeadline */
        $InvPressBarHeadline = $this->systemConfigService->get('InvPressBar.config.headline');
        $event->setParameter('InvPressBarHeadline', $InvPressBarHeadline);

        /** @var string $InvPressBarLogo1 */
        $InvPressBarLogo1 = $this->systemConfigService->get('InvPressBar.config.logo1');
        $event->setParameter('InvPressBarLogo1', $InvPressBarLogo1);

        /** @var string $InvPressBarLogo2 */
        $InvPressBarLogo2 = $this->systemConfigService->get('InvPressBar.config.logo2');
        $event->setParameter('InvPressBarLogo2', $InvPressBarLogo2);

        /** @var string $InvPressBarLogo3 */
        $InvPressBarLogo3 = $this->systemConfigService->get('InvPressBar.config.logo3');
        $event->setParameter('InvPressBarLogo3', $InvPressBarLogo3);

        /** @var string $InvPressBarLogo4 */
        $InvPressBarLogo4 = $this->systemConfigService->get('InvPressBar.config.logo4');
        $event->setParameter('InvPressBarLogo4', $InvPressBarLogo4);

        /** @var string $InvPressBarLogo5 */
        $InvPressBarLogo5 = $this->systemConfigService->get('InvPressBar.config.logo5');
        $event->setParameter('InvPressBarLogo5', $InvPressBarLogo5);

        /** @var string $InvPressBarLogo6 */
        $InvPressBarLogo6 = $this->systemConfigService->get('InvPressBar.config.logo6');
        $event->setParameter('InvPressBarLogo6', $InvPressBarLogo6);

    }
}
