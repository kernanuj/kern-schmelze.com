<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\SeoUrlHelper;

use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;

class SeoUrlHelper implements SeoUrlHelperInterface
{
    /** @var EntityRepositoryInterface */
    private $salesChannelDomainRepository;

    /** @var SalesChannelContextFactory */
    private $salesChannelContextFactory;

    /** @var SeoUrlPlaceholderHandlerInterface */
    private $seoUrlPlaceholderHandler;

    /** @var null|ParameterBag */
    private $currentRequestAttributes;

    public function __construct(
        EntityRepositoryInterface $salesChannelDomainRepository,
        SalesChannelContextFactory $salesChannelContextFactory,
        RequestStack $requestStack,
        SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler
    ) {
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
        $this->salesChannelContextFactory   = $salesChannelContextFactory;
        $this->seoUrlPlaceholderHandler     = $seoUrlPlaceholderHandler;

        $this->currentRequestAttributes = $requestStack->getCurrentRequest() !== null ? $requestStack->getCurrentRequest()->attributes : null;
    }

    public function getSeoUrlFromDomainId(array $urlParameters, string $salesChannelDomainId, SalesChannelContext $salesChannelContext, string $target = 'frontend.navigation.page'): string
    {
        $salesChannelDomain = $this->salesChannelDomainRepository->search(new Criteria([$salesChannelDomainId]), $salesChannelContext->getContext())->first();

        return $this->resolveUrl($urlParameters, $target, $salesChannelDomain, $salesChannelContext);
    }

    public function getSeoUrl(array $urlParameters, SalesChannelDomainEntity $salesChannelDomain, string $target = 'frontend.navigation.page'): string
    {
        $salesChannelContext = $this->getSalesChannelContext($salesChannelDomain->getSalesChannelId());

        return $this->resolveUrl($urlParameters, $target, $salesChannelDomain, $salesChannelContext);
    }

    private function getSalesChannelContext(string $salesChannelId): SalesChannelContext
    {
        if ($this->currentRequestAttributes && $this->currentRequestAttributes->get('sw-sales-channel-context') instanceof SalesChannelContext) {
            return $this->currentRequestAttributes->get('sw-sales-channel-context');
        }

        return $this->salesChannelContextFactory->create(Uuid::randomHex(), $salesChannelId);
    }

    private function resolveUrl(array $urlParameters, string $target, SalesChannelDomainEntity $salesChannelDomain, SalesChannelContext $salesChannelContext): string
    {
        if ($this->currentRequestAttributes === null) {
            return '';
        }

        $baseUrl = $this->currentRequestAttributes->get(RequestTransformer::SALES_CHANNEL_ABSOLUTE_BASE_URL) . $this->currentRequestAttributes->get(RequestTransformer::SALES_CHANNEL_BASE_URL);

        if (empty($baseUrl) || !strpos($baseUrl, 'https') || !strpos($baseUrl, 'http')) {
            $baseUrl = $salesChannelDomain->getUrl();
        }

        return $this->seoUrlPlaceholderHandler->replace(
            $this->seoUrlPlaceholderHandler->generate($target, $urlParameters),
            $baseUrl,
            $salesChannelContext
        );
    }
}
