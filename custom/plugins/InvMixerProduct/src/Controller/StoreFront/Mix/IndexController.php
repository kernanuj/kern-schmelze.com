<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\Service\MixServiceInterface;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class IndexController
 *
 * @package InvMixerProduct\Controller\StoreFront\Mix
 *
 * @RouteScope(scopes={"storefront"})
 * @Route("/mix", methods={"GET"}, name="invMixerProduct.storeFront.mix.index")
 */
class IndexController extends MixController
{

    /**
     * @var ProductListingLoader
     */
    private $productListingLoader;

    /**
     * @var MixServiceInterface
     */
    private $mixService;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var GenericPageLoaderInterface
     */
    private $pageLoader;

    /**
     * IndexController constructor.
     * @param ProductListingLoader $productListingLoader
     * @param MixServiceInterface $mixService
     * @param SessionInterface $session
     * @param GenericPageLoaderInterface $pageLoader
     */
    public function __construct(
        ProductListingLoader $productListingLoader,
        MixServiceInterface $mixService,
        SessionInterface $session,
        GenericPageLoaderInterface $pageLoader
    ) {
        $this->productListingLoader = $productListingLoader;
        $this->mixService = $mixService;
        $this->session = $session;
        $this->pageLoader = $pageLoader;
    }


    /**
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @param Context $context
     * @return Response
     * @throws \Exception
     */
    public function __invoke(Request $request, SalesChannelContext $salesChannelContext, Context $context)
    {

        $productListing = $this->productListingLoader->load(
            new Criteria(),
            $salesChannelContext
        );

        return $this->renderStorefront(
            '@InvMixerProduct/storefront/page/mix.index.html.twig',
            [
                'page' => $this->pageLoader->load($request, $salesChannelContext),
                'productListing' => $productListing,
            ]
        );
    }
}
