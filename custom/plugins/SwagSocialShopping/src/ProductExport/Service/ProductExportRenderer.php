<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagSocialShopping\ProductExport\Service;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\ProductExport\ProductExportEntity;
use Shopware\Core\Content\ProductExport\Service\ProductExportRendererInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagSocialShopping\Component\Validation\NetworkProductValidator;
use SwagSocialShopping\DataAbstractionLayer\Entity\SocialShoppingSalesChannelEntity;
use SwagSocialShopping\SwagSocialShopping;

class ProductExportRenderer implements ProductExportRendererInterface
{
    /**
     * @var ProductExportRendererInterface
     */
    private $productExportRenderer;

    /**
     * @var NetworkProductValidator
     */
    private $networkProductValidator;

    /**
     * @var SocialShoppingSalesChannelEntity|null
     */
    private $socialShoppingSalesChannel;

    /**
     * @var EntityRepositoryInterface
     */
    private $socialShoppingSalesChannelRepository;

    /**
     * @var int
     */
    private $offset = 0;

    public function __construct(
        ProductExportRendererInterface $productExportRenderer,
        NetworkProductValidator $networkProductValidator,
        EntityRepositoryInterface $socialShoppingSalesChannelRepository
    ) {
        $this->productExportRenderer = $productExportRenderer;
        $this->networkProductValidator = $networkProductValidator;
        $this->socialShoppingSalesChannelRepository = $socialShoppingSalesChannelRepository;
    }

    public function renderHeader(
        ProductExportEntity $productExport,
        SalesChannelContext $salesChannelContext
    ): string {
        return $this->productExportRenderer->renderHeader($productExport, $salesChannelContext);
    }

    public function renderFooter(
        ProductExportEntity $productExport,
        SalesChannelContext $salesChannelContext
    ): string {
        return $this->productExportRenderer->renderFooter($productExport, $salesChannelContext);
    }

    public function renderBody(
        ProductExportEntity $productExport,
        SalesChannelContext $salesChannelContext,
        array $data
    ): string {
        $product = $data['product'];
        $socialSalesChannel = $this->getSocialShoppingSalesChannel($productExport, $salesChannelContext);

        if ($socialSalesChannel === null) {
            return $this->productExportRenderer->renderBody($productExport, $salesChannelContext, $data);
        }

        ++$this->offset;
        $data['socialShoppingSalesChannel'] = $socialSalesChannel;

        $validationHasErrors = $this->networkProductValidator->executeValidators(
            new ProductCollection([$product]),
            $socialSalesChannel,
            $this->offset === 1
        );

        if (!$validationHasErrors) {
            return $this->productExportRenderer->renderBody($productExport, $salesChannelContext, $data);
        }

        return '';
    }

    private function getSocialShoppingSalesChannel(
        ProductExportEntity $productExport,
        SalesChannelContext $salesChannelContext
    ): ?SocialShoppingSalesChannelEntity {
        if ($productExport->getSalesChannel()->getTypeId() !== SwagSocialShopping::SALES_CHANNEL_TYPE_SOCIAL_SHOPPING) {
            return null;
        }

        if ($this->socialShoppingSalesChannel !== null) {
            return $this->socialShoppingSalesChannel;
        }

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('salesChannelId', $productExport->getSalesChannelId()))
            ->addFilter(new EqualsFilter('salesChannelDomainId', $productExport->getSalesChannelDomainId()))
            ->addFilter(new EqualsFilter('productStreamId', $productExport->getProductStreamId()));

        $searchResult = $this->socialShoppingSalesChannelRepository->search($criteria, $salesChannelContext->getContext());
        $this->socialShoppingSalesChannel = $searchResult->first();

        return $this->socialShoppingSalesChannel;
    }
}
