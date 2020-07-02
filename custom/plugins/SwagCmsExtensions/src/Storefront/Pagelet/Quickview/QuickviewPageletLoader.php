<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Storefront\Pagelet\Quickview;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Product\Configurator\ProductPageConfiguratorLoader;
use Shopware\Storefront\Page\Product\ProductLoader;
use Shopware\Storefront\Page\Product\Review\ProductReviewLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class QuickviewPageletLoader implements QuickviewPageletLoaderInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ProductLoader
     */
    protected $productLoader;

    /**
     * @var ProductReviewLoader
     */
    protected $productReviewLoader;

    /**
     * @var ProductPageConfiguratorLoader
     */
    protected $productPageConfiguratorLoader;

    /**
     * @var SalesChannelRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ProductLoader $productLoader,
        ProductReviewLoader $productReviewLoader,
        ProductPageConfiguratorLoader $productPageConfiguratorLoader,
        SalesChannelRepositoryInterface $productRepository
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->productLoader = $productLoader;
        $this->productReviewLoader = $productReviewLoader;
        $this->productPageConfiguratorLoader = $productPageConfiguratorLoader;
        $this->productRepository = $productRepository;
    }

    public function load(Request $request, SalesChannelContext $salesChannelContext): QuickviewPageletInterface
    {
        $product = $this->productLoader->load($this->getProductId($request, $salesChannelContext), $salesChannelContext);

        $listingProductId = $request->get('productId');
        $reviews = $this->productReviewLoader->load($request, $salesChannelContext);
        $configuratorSettings = $this->productPageConfiguratorLoader->load($product, $salesChannelContext);

        $pagelet = new QuickviewPagelet($product, $listingProductId, $reviews, $configuratorSettings);

        $this->eventDispatcher->dispatch(new QuickviewPageletLoadedEvent($pagelet, $salesChannelContext, $request));

        return $pagelet;
    }

    protected function getProductId(Request $request, SalesChannelContext $salesChannelContext): string
    {
        $productId = $request->get('productId');

        if (!$productId) {
            throw new MissingRequestParameterException('productId', '/productId');
        }

        $productId = $this->findBestVariant($productId, $salesChannelContext);

        return $productId;
    }

    private function findBestVariant(string $productId, SalesChannelContext $salesChannelContext): string
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('product.parentId', $productId))
            ->addSorting(new FieldSorting('product.price'))
            ->setLimit(1);

        $variantId = $this->productRepository->searchIds($criteria, $salesChannelContext)->firstId();

        return $variantId ?? $productId;
    }
}
