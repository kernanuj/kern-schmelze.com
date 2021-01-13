<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Storefront\Pagelet\Quickview;

use Shopware\Core\Content\Product\SalesChannel\Detail\ProductDetailRoute;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Product\Configurator\ProductCombinationFinder;
use Shopware\Storefront\Page\Product\Configurator\ProductPageConfiguratorLoader;
use Shopware\Storefront\Page\Product\Review\ProductReviewLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class QuickviewVariantPageletLoader extends QuickviewPageletLoader
{
    /**
     * @var ProductCombinationFinder
     */
    private $productCombinationFinder;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ProductDetailRoute $productDetailRoute,
        ProductReviewLoader $productReviewLoader,
        ProductPageConfiguratorLoader $productPageConfiguratorLoader,
        SalesChannelRepositoryInterface $productRepository,
        ProductCombinationFinder $productCombinationFinder
    ) {
        parent::__construct($eventDispatcher, $productDetailRoute, $productReviewLoader, $productPageConfiguratorLoader, $productRepository);

        $this->productCombinationFinder = $productCombinationFinder;
    }

    protected function getProductId(Request $request, SalesChannelContext $salesChannelContext): string
    {
        $parentId = $request->get('parentId');
        $switchedOption = $request->query->get('switched');
        $newOptions = \json_decode($request->query->get('options'), true);

        $combination = $this->productCombinationFinder->find($parentId, $switchedOption, $newOptions, $salesChannelContext);

        return $combination->getVariantId();
    }
}
