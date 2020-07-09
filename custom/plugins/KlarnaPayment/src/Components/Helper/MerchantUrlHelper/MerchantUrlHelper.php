<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\MerchantUrlHelper;

use KlarnaPayment\Components\ConfigReader\ConfigReaderInterface;
use KlarnaPayment\Components\Helper\SeoUrlHelper\SeoUrlHelperInterface;
use LogicException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Symfony\Component\HttpFoundation\Request;

class MerchantUrlHelper implements MerchantUrlHelperInterface
{
    /** @var SeoUrlHelperInterface */
    private $seoUrlHelper;

    /** @var ConfigReaderInterface */
    private $configReader;

    /** @var EntityRepositoryInterface */
    private $salesChannelDomainRepository;

    public function __construct(SeoUrlHelperInterface $seoUrlHelper, ConfigReaderInterface $configReader, EntityRepositoryInterface $salesChannelDomainRepository)
    {
        $this->seoUrlHelper                 = $seoUrlHelper;
        $this->configReader                 = $configReader;
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
    }

    public function getMerchantUrls(SalesChannelDomainEntity $salesChannelDomain): array
    {
        $configuration = $this->configReader->read($salesChannelDomain->getSalesChannelId());

        $result = [
            'place_order' => $this->seoUrlHelper->getSeoUrl([], $salesChannelDomain, 'frontend.klarna.instantShopping.placeOrder'),
            'update'      => $this->seoUrlHelper->getSeoUrl([], $salesChannelDomain, 'frontend.klarna.instantShopping.update'),
        ];

        if ($configuration->get('termsCategory')) {
            $termsUrl = $this->seoUrlHelper->getSeoUrl(
                ['navigationId' => (string) $configuration->get('termsCategory')], $salesChannelDomain
            );

            if (!empty($termsUrl)) {
                $result['terms'] = $termsUrl;
            }
        }

        return $result;
    }

    public function getSalesChannelDomainFromRequest(Request $request, Context $context): SalesChannelDomainEntity
    {
        $salesChannelDomainId = $request->attributes->get('sw-domain-id');

        if (!$salesChannelDomainId) {
            throw new LogicException('No sales channel domain ID in request');
        }

        $domain = $this->salesChannelDomainRepository->search(new Criteria([$salesChannelDomainId]), $context)->first();

        if (!$domain) {
            throw new LogicException(sprintf('Sales channel domain with ID %s not found', $salesChannelDomainId));
        }

        return $domain;
    }
}
