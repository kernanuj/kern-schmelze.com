<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class IndexController
 *
 * @package InvMixerProduct\Controller\StoreFront\Mix
 *
 * @RouteScope(scopes={"storefront"})
 * @Route("/mix", methods={"GET"}, name="invMixerProduct.storeFront.mix.index")
 */
class IndexController extends StorefrontController
{

    /**
     * @var ProductListingLoader
     */
    private $productListingLoader;

    /**
     * IndexController constructor.
     * @param ProductListingLoader $productListingLoader
     */
    public function __construct(ProductListingLoader $productListingLoader)
    {
        $this->productListingLoader = $productListingLoader;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return Response
     */
    public function __invoke(SalesChannelContext $salesChannelContext)
    {

        $productListing = $this->productListingLoader->load(
            new Criteria(),
            $salesChannelContext
        );

        return $this->renderStorefront(
            '@InvMixerProduct/storefront/page/mix.index.html.twig',
            [
                'productListing' => $productListing
            ]
        );
    }
}
