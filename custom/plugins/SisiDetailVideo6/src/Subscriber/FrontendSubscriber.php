<?php

declare(strict_types=1);

namespace Sisi\SisiDetailVideo6\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class FrontendSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $mediaRepository;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService, EntityRepositoryInterface $mediaRepository)
    {
        $this->systemConfigService = $systemConfigService;
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        // Return the events to listen to as array like this:  <event to listen to> => <method to execute>
        return [
            StorefrontRenderEvent::class => 'onStorefrontRender'
        ];
    }

    public function onStorefrontRender(StorefrontRenderEvent $event): void
    {
        $context = $event->getContext();

        $pluginConfig = $this->systemConfigService->get('SisiDetailVideo6.config');

        if ($pluginConfig['imgLink']) {
            $previewImage = $this->findMediaById($pluginConfig['imgLink'], $context);

            $event->setParameter('sisiDetailVideoPreview', $previewImage);
        }
    }

    /**
     * @param string $mediaId
     * @param $context
     * @return string
     */
    public function findMediaById(string $mediaId, $context): string
    {
        $criteria = new Criteria([$mediaId]);
        $criteria->addAssociation('mediaFolder');
        $currentMedia = $this->mediaRepository
            ->search($criteria, $context)
            ->get($mediaId);

        $mediaUrl = "";
        if ($currentMedia) {
            $mediaUrl = $currentMedia->getUrl();
        }

        return $mediaUrl;
    }
}
