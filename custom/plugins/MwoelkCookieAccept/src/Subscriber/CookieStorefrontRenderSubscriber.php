<?php declare(strict_types=1);

namespace Mwoelk\CookieAccept\Subscriber;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CookieStorefrontRenderSubscriber implements EventSubscriberInterface
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorefrontRenderEvent::class => 'onStorefrontRender'
        ];
    }

    public function onStorefrontRender(StorefrontRenderEvent $event)
    {
        $salesChannelId = $event->getSalesChannelContext()->getSalesChannel()->getId();
        $config = $this->getCookieConfig($salesChannelId);

        $event->setParameter('mwoelkCookiePluginOptions', $config);
    }

    private function getCookieConfig(string $salesChannelId): array
    {
        return [
            'active' => $this->systemConfigService->get('MwoelkCookieAccept.config.active', $salesChannelId),
            'reloadPage' => $this->systemConfigService->get('MwoelkCookieAccept.config.reloadPage', $salesChannelId)
        ];
    }
}