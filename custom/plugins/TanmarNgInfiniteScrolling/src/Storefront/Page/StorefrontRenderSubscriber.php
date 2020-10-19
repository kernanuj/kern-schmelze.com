<?php

declare(strict_types=1);

namespace Tanmar\NgInfiniteScrolling\Storefront\Page;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Tanmar\NgInfiniteScrolling\Storefront\BaseSubscriber;
use Tanmar\NgInfiniteScrolling\Service\ConfigService;

class StorefrontRenderSubscriber extends BaseSubscriber implements EventSubscriberInterface {

    public function __construct(ConfigService $configService) {
        parent::__construct($configService);
    }

    public static function getSubscribedEvents(): array {
        return [
            StorefrontRenderEvent::class => 'onStorefrontRender'
        ];
    }

    public function onStorefrontRender(StorefrontRenderEvent $event): void {
        $config = $this->getConfig();
        if (!is_null($config)) {
            $isActive = $config->isActive();
            $pages = $config->getPages();
            try {
                $productData = $this->getExtension($event);
                if (!is_null($productData) && $isActive) {
                    $productData->assign([
                        'isActive' => true,
                        'pages' => $pages
                    ]);
                }
                $this->addExtension($event, $productData);
            } catch (\Exception $e) {
                
            }
        }
    }

}
