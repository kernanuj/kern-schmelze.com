<?php

declare (strict_types=1);
namespace Maxia\MaxiaLazyLoader6\Subscriber;

use Maxia\MaxiaLazyLoader6\Components\Config;
use Maxia\MaxiaLazyLoader6\Components\Dom\DomFilter;
use Maxia\MaxiaLazyLoader6\Components\Dom\Filter\ImgTagFilter;
use Maxia\MaxiaLazyLoader6\Components\Dom\Filter\PictureTagFilter;
use Maxia\MaxiaLazyLoader6\Components\Dom\Filter\StyleAttributeFilter;
use Monolog\Logger;
use Shopware\Core\Content\Seo\SeoResolverInterface;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\HttpFoundation\Request;
use _PhpScoperfd240ab1f7e6\voku\helper\HtmlMin;
/**
 * @package Maxia\LazyLoader6\Subscriber
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
     * @var Logger
     */
    private $logger;
    /**
     * @var Config
     */
    private $configService;
    /**
     * @var array
     */
    private $config;
    /**
     * @var DomFilter
     */
    private $domFilter;
    /**
     * @param SeoResolverInterface $seoResolver
     * @param Logger $logger
     * @param Config $configService
     * @param DomFilter $domFilter
     */
    public function __construct(\Shopware\Core\Content\Seo\SeoResolverInterface $seoResolver, \Monolog\Logger $logger, \Maxia\MaxiaLazyLoader6\Components\Config $configService, \Maxia\MaxiaLazyLoader6\Components\Dom\DomFilter $domFilter)
    {
        $this->seoResolver = $seoResolver;
        $this->logger = $logger;
        $this->configService = $configService;
        $this->domFilter = $domFilter;
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
        // exclude some URLs
        $url = $this->getSeoUrl($event->getRequest(), $this->salesChannelContext);
        foreach ($this->config['blacklistUrls'] as $pattern) {
            if (\preg_match('#' . $pattern . '$#', $url)) {
                if ($this->config['debugLogging']) {
                    $this->logger->addInfo('Blacklist pattern \'' . $pattern . '\' matches on ' . $url);
                }
                $this->config['active'] = \false;
                break;
            }
        }
        $this->salesChannelContext->addExtension('maxiaLazyLoader', new \Shopware\Core\Framework\Struct\ArrayEntity($this->config));
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
        if ($this->config['debugLogging']) {
            $stopwatch = new \Symfony\Component\Stopwatch\Stopwatch();
        }
        if ($this->config['active']) {
            try {
                // lazify html
                $this->domFilter->setBlacklist($this->config['blacklistSelectors']);
                $this->domFilter->setFilters($this->getFilters($this->config));
                if ($this->config['debugLogging']) {
                    $this->logger->addInfo('Start lazify filter ' . $event->getRequest()->getUri());
                }
                if (isset($stopwatch)) {
                    $stopwatch->start('lazify');
                }
                $html = $this->domFilter->process($event->getResponse()->getContent());
                if ($this->config['debugLogging']) {
                    if (isset($stopwatch)) {
                        /** @var \Symfony\Component\Stopwatch\StopwatchEvent $event */
                        $watch = $stopwatch->stop('lazify');
                        $this->logger->addInfo('Lazify duration: ' . $watch->getDuration() . 'ms');
                    }
                    if ($this->domFilter->getErrors()) {
                        $this->logger->addError('Errors occurred when parsing HTML:');
                        foreach ($this->domFilter->getErrors() as $error) {
                            $this->logger->addError($error);
                        }
                    }
                }
                if ($html) {
                    $event->getResponse()->setContent($html);
                }
            } catch (\Exception $e) {
                $this->logger->addError((string) $e);
            }
            // minify html
            if ($this->config['minifyHtml']) {
                if ($this->config['debugLogging']) {
                    $this->logger->addInfo('Start html minify ' . $event->getRequest()->getUri());
                }
                if (isset($stopwatch)) {
                    $stopwatch->start('minifyHtml');
                }
                $htmlMin = new \_PhpScoperfd240ab1f7e6\voku\helper\HtmlMin();
                $htmlMin->doSortHtmlAttributes(\false);
                $htmlMin->doSortCssClassNames(\false);
                $html = $htmlMin->minify($event->getResponse()->getContent());
                $event->getResponse()->setContent($html);
                if (isset($stopwatch)) {
                    $watch = $stopwatch->stop('minifyHtml');
                    $this->logger->addInfo('HTML minify duration: ' . $watch->getDuration() . 'ms');
                }
            }
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
    /**
     * @param array $config
     * @return array
     */
    protected function getFilters(array $config)
    {
        $options = ['lazyClass' => 'maxia-lazy-image', 'noscriptFallback' => $config['outputFallback'], 'placeholder' => "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="];
        $filters = [new \Maxia\MaxiaLazyLoader6\Components\Dom\Filter\PictureTagFilter($options), new \Maxia\MaxiaLazyLoader6\Components\Dom\Filter\ImgTagFilter($options), new \Maxia\MaxiaLazyLoader6\Components\Dom\Filter\StyleAttributeFilter($options)];
        return $filters;
    }
}
