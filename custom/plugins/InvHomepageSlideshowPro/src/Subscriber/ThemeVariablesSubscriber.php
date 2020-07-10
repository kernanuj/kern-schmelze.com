<?php
namespace InvHomepageSlideshowPro\Subscriber;

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
        /** @var string $configMinHeight */
        $configMinHeight = $this->systemConfigService->get('InvHomepageSlideshowPro.config.minHeight', $event->getSalesChannelId());
        if ($configMinHeight) {
            $event->addVariable('inv-homepage-slideshow-pro-min-height', $configMinHeight);
        }
    }

    public function onStorefrontRender(StorefrontRenderEvent $event)
    {
        /** @var bool $InvHomepageSlideshowProStatus */
        $InvHomepageSlideshowProStatus = $this->systemConfigService->get('InvHomepageSlideshowPro.config.status');
        $event->setParameter('InvHomepageSlideshowProStatus', $InvHomepageSlideshowProStatus);

        /** @var string $InvHomepageSlideshowProSlide1 */
        $InvHomepageSlideshowProSlide1 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.slide1');
        $event->setParameter('InvHomepageSlideshowProSlide1', $InvHomepageSlideshowProSlide1);

        /** @var string $InvHomepageSlideshowProImage1 */
        $InvHomepageSlideshowProImage1 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.image1');
        $event->setParameter('InvHomepageSlideshowProImage1', $InvHomepageSlideshowProImage1);

        /** @var string $InvHomepageSlideshowProImage11 */
        $InvHomepageSlideshowProImage11 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.image11');
        $event->setParameter('InvHomepageSlideshowProImage11', $InvHomepageSlideshowProImage11);

        /** @var string $InvHomepageSlideshowProSlide2 */
        $InvHomepageSlideshowProSlide2 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.slide2');
        $event->setParameter('InvHomepageSlideshowProSlide2', $InvHomepageSlideshowProSlide2);

        /** @var string $InvHomepageSlideshowProImage2 */
        $InvHomepageSlideshowProImage2 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.image2');
        $event->setParameter('InvHomepageSlideshowProImage2', $InvHomepageSlideshowProImage2);

        /** @var string $InvHomepageSlideshowProImage22 */
        $InvHomepageSlideshowProImage22 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.image22');
        $event->setParameter('InvHomepageSlideshowProImage22', $InvHomepageSlideshowProImage22);

        /** @var string $InvHomepageSlideshowProSlide3 */
        $InvHomepageSlideshowProSlide3 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.slide3');
        $event->setParameter('InvHomepageSlideshowProSlide3', $InvHomepageSlideshowProSlide3);

        /** @var string $InvHomepageSlideshowProImage3 */
        $InvHomepageSlideshowProImage3 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.image3');
        $event->setParameter('InvHomepageSlideshowProImage3', $InvHomepageSlideshowProImage3);

        /** @var string $InvHomepageSlideshowProImage33 */
        $InvHomepageSlideshowProImage33 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.image33');
        $event->setParameter('InvHomepageSlideshowProImage33', $InvHomepageSlideshowProImage33);

        /** @var string $InvHomepageSlideshowProSlide4 */
        $InvHomepageSlideshowProSlide4 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.slide4');
        $event->setParameter('InvHomepageSlideshowProSlide4', $InvHomepageSlideshowProSlide4);

        /** @var string $InvHomepageSlideshowProImage4 */
        $InvHomepageSlideshowProImage4 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.image4');
        $event->setParameter('InvHomepageSlideshowProImage4', $InvHomepageSlideshowProImage4);

        /** @var string $InvHomepageSlideshowProImage44 */
        $InvHomepageSlideshowProImage44 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.image44');
        $event->setParameter('InvHomepageSlideshowProImage44', $InvHomepageSlideshowProImage44);

        /** @var string $InvHomepageSlideshowProSlide5 */
        $InvHomepageSlideshowProSlide5 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.slide5');
        $event->setParameter('InvHomepageSlideshowProSlide5', $InvHomepageSlideshowProSlide5);

        /** @var string $InvHomepageSlideshowProImage5 */
        $InvHomepageSlideshowProImage5 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.image5');
        $event->setParameter('InvHomepageSlideshowProImage5', $InvHomepageSlideshowProImage5);

        /** @var string $InvHomepageSlideshowProImage55 */
        $InvHomepageSlideshowProImage55 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.image55');
        $event->setParameter('InvHomepageSlideshowProImage55', $InvHomepageSlideshowProImage55);
    }
}
