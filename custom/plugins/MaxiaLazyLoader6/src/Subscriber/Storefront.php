<?php declare(strict_types=1);

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

/**
 * @package Maxia\LazyLoader6\Subscriber
 */
class Storefront implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return[
            KernelEvents::RESPONSE => 'onResponse',
            StorefrontRenderEvent::class => 'onRenderStorefront',
        ];
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
    public function __construct(
        SeoResolverInterface $seoResolver,
        Logger $logger,
        Config $configService,
        DomFilter $domFilter
    ) {
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
    public function onRenderStorefront(StorefrontRenderEvent $event): void
    {
        $this->salesChannelContext = $event->getSalesChannelContext();

        $this->config = $this->configService->getConfig();

        // exclude some URLs
        $url = $this->getSeoUrl($event->getRequest(), $this->salesChannelContext);

        foreach ($this->config['blacklistUrls'] as $pattern) {

            if (preg_match('#^'.$pattern.'$#', $url)) {
                $this->config['active'] = false;
                break;
            }
        }

        $this->salesChannelContext->addExtension('maxiaLazyLoader', new ArrayEntity($this->config));
    }

    /**
     * Update storefront response.
     *
     * @param ResponseEvent $event
     */
    public function onResponse(ResponseEvent $event): void
    {
        if (!$this->salesChannelContext) {
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

        if (!preg_match('/^\s*<.*?>$/ism', $event->getResponse()->getContent())) {
            return;
        }

        // lazify html
        if ($this->config['active']) {
            try {
                $this->domFilter->setBlacklist($this->config['blacklistSelectors']);
                $this->domFilter->setFilters($this->getFilters($this->config));

                $html = $this->domFilter->process($event->getResponse()->getContent());

                if ($this->config['debugLogging'] && $this->domFilter->getErrors()) {
                    foreach ($this->domFilter->getErrors() as $error) {
                        $this->logger->addError($error);
                    }
                }

                if ($html) {
                    $event->getResponse()->setContent($html);
                }
            } catch (\Exception $e) {
                $this->logger->addError((string)$e);
            }
        }

        // minify html
        if ($this->config['minifyHtml']) {
            $event->getResponse()->setContent(
                preg_replace(
                    '#(?ix)(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:textarea|pre|script)\b))*+)(?:<(?>textarea|pre|script)\b|\z))#',
                    ' ',
                    $event->getResponse()->getContent()
                )
            );
        }
    }

    /**
     * @param Request $request
     * @param SalesChannelContext $context
     * @return string
     */
    protected function getSeoUrl(Request $request, SalesChannelContext $context)
    {
        $url = $this->seoResolver->resolveSeoPath(
            $context->getSalesChannel()->getLanguageId(),
            $context->getSalesChannel()->getId(),
            $request->getPathInfo()
        );

        if ($url && !empty($url['canonicalPathInfo'])) {
            return $url['canonicalPathInfo'];
        }

        return $request->getUri();
    }

    /**
     * @param array $config
     * @return array
     */
    protected function getFilters(array $config)
    {
        $options = [
            'lazyClass' => 'maxia-lazy-image',
            'noscriptFallback' => $config['outputFallback'],
            'placeholder' => "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==",
        ];

        $filters = [
            new PictureTagFilter($options),
            new ImgTagFilter($options),
            new StyleAttributeFilter($options)
        ];

        return $filters;
    }
}