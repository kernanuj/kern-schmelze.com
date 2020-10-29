<?php
declare (strict_types=1);
namespace InvUserlikechat\Subscriber;
use InvUserlikechat\Components\Config;
use Monolog\Logger;
use Shopware\Core\Content\Seo\SeoResolverInterface;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\HttpFoundation\Request;
/**
 * @package InvUserlikechat\Subscriber
 */
class Storefront implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public static function getSubscribedEvents() : array
    {
        return [\Symfony\Component\HttpKernel\KernelEvents::RESPONSE => 'onResponse', \Shopware\Storefront\Event\StorefrontRenderEvent::class => 'onRenderStorefront'];
    }
    /**
     * @var SeoResolverInterface
     */
    private $seoResolver;
    /**
     * @var SalesChannelContext
     */
    private $salesChannelContext;
    /**
     * @var Config
     */
    private $configService;
    /**
     * @var array
     */
    private $config;
    /**
     * @param SeoResolverInterface $seoResolver
     * @param Config $configService
     */
    public function __construct(\Shopware\Core\Content\Seo\SeoResolverInterface $seoResolver, \InvUserlikechat\Components\Config $configService)
    {
        $this->seoResolver = $seoResolver;
        $this->configService = $configService;
    }
    /**
     * Add plugin config to twig view.
     *
     * @param StorefrontRenderEvent $event
     */
    public function onRenderStorefront(\Shopware\Storefront\Event\StorefrontRenderEvent $event) : void
    {
        $this->salesChannelContext = $event->getSalesChannelContext();
        $this->config = $this->configService->getConfig();

        $url = $this->getSeoUrl($event->getRequest(), $this->salesChannelContext);

        // exclude URLs
        if (!empty($this->config['noDisplayUrls'])) {
            foreach ($this->config['noDisplayUrls'] as $pattern) {
                if (\preg_match('#' . $pattern . '$#', $url)) {
                    $this->config['status'] = \false;
                    break;
                }
            }
        }

        $this->salesChannelContext->addExtension('invUserlikechat', new \Shopware\Core\Framework\Struct\ArrayEntity($this->config));
    }
    /**
     * Update storefront response.
     *
     * @param ResponseEvent $event
     */
    public function onResponse(\Symfony\Component\HttpKernel\Event\ResponseEvent $event) : void
    {
        if (!$this->salesChannelContext) {
            return;
        }
        if (!$event->isMasterRequest()) {
            return;
        }
        $headers = $event->getResponse()->headers;
        // exclude non-html responses
        if (!$headers || !$headers->get('content-type')) {
            return;
        }
        if ($headers->get('content-type') != 'text/html') {
            return;
        }
        if (!\preg_match('/^\\s*<.*?>$/ism', $event->getResponse()->getContent())) {
            return;
        }
        if ($this->config['status']) {
        }
    }
    /**
     * @param Request $request
     * @param SalesChannelContext $context
     * @return string
     */
    protected function getSeoUrl(\Symfony\Component\HttpFoundation\Request $request, \Shopware\Core\System\SalesChannel\SalesChannelContext $context)
    {
        $url = $this->seoResolver->resolveSeoPath($context->getSalesChannel()->getLanguageId(), $context->getSalesChannel()->getId(), $request->getPathInfo());
        if ($url && !empty($url['canonicalPathInfo'])) {
            $url = $url['canonicalPathInfo'];
        } else {
            $url = $request->getUri();
        }
        $url = \parse_url($url);
        $path = $url['path'];
        if (isset($url['query']) && $url['query']) {
            $path .= '?' . $url['query'];
        }
        return $path;
    }
}
