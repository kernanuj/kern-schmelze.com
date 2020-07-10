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
        /** @var string $configRightBackgroundColor1 */
        $configRightBackgroundColor1 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.leftbackgroundcolor1', $event->getSalesChannelId());
        if ($configRightBackgroundColor1) {
            $event->addVariable('inv-homepage-slideshow-pro-left-background-color1', $configRightBackgroundColor1);
        }

        /** @var string $configLeftBackgroundColor1 */
        $configLeftBackgroundColor1 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.rightbackgroundcolor1', $event->getSalesChannelId());
        if ($configLeftBackgroundColor1) {
            $event->addVariable('inv-homepage-slideshow-pro-right-background-color1', $configLeftBackgroundColor1);
        }

        /** @var string $configRightBackgroundColor2 */
        $configRightBackgroundColor2 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.leftbackgroundcolor2', $event->getSalesChannelId());
        if ($configRightBackgroundColor2) {
            $event->addVariable('inv-homepage-slideshow-pro-left-background-color2', $configRightBackgroundColor2);
        }

        /** @var string $configLeftBackgroundColor2 */
        $configLeftBackgroundColor2 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.rightbackgroundcolor2', $event->getSalesChannelId());
        if ($configLeftBackgroundColor2) {
            $event->addVariable('inv-homepage-slideshow-pro-right-background-color2', $configLeftBackgroundColor2);
        }

        /** @var string $configRightBackgroundColor3 */
        $configRightBackgroundColor3 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.leftbackgroundcolor3', $event->getSalesChannelId());
        if ($configRightBackgroundColor3) {
            $event->addVariable('inv-homepage-slideshow-pro-left-background-color3', $configRightBackgroundColor3);
        }

        /** @var string $configLeftBackgroundColor3 */
        $configLeftBackgroundColor3 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.rightbackgroundcolor3', $event->getSalesChannelId());
        if ($configLeftBackgroundColor3) {
            $event->addVariable('inv-homepage-slideshow-pro-right-background-color3', $configLeftBackgroundColor3);
        }

        /** @var string $configRightBackgroundColor4 */
        $configRightBackgroundColor4 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.leftbackgroundcolor4', $event->getSalesChannelId());
        if ($configRightBackgroundColor4) {
            $event->addVariable('inv-homepage-slideshow-pro-left-background-color4', $configRightBackgroundColor4);
        }

        /** @var string $configLeftBackgroundColor4 */
        $configLeftBackgroundColor4 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.rightbackgroundcolor4', $event->getSalesChannelId());
        if ($configLeftBackgroundColor4) {
            $event->addVariable('inv-homepage-slideshow-pro-right-background-color4', $configLeftBackgroundColor4);
        }

        /** @var string $configRightBackgroundColor5 */
        $configRightBackgroundColor5 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.leftbackgroundcolor5', $event->getSalesChannelId());
        if ($configRightBackgroundColor5) {
            $event->addVariable('inv-homepage-slideshow-pro-left-background-color5', $configRightBackgroundColor5);
        }

        /** @var string $configLeftBackgroundColor5 */
        $configLeftBackgroundColor5 = $this->systemConfigService->get('InvHomepageSlideshowPro.config.rightbackgroundcolor5', $event->getSalesChannelId());
        if ($configLeftBackgroundColor5) {
            $event->addVariable('inv-homepage-slideshow-pro-right-background-color5', $configLeftBackgroundColor5);
        }

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
