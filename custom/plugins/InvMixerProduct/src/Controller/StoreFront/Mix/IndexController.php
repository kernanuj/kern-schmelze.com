<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\Service\MixServiceInterface;
use InvMixerProduct\Service\ProductListingProviderInterface;
use Shopware\Core\Framework\Context;
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
     * @var ProductListingProviderInterface
     */
    private $productListingProvider;

    /**
     * IndexController constructor.
     * @param MixServiceInterface $mixService
     * @param SessionInterface $session
     * @param GenericPageLoaderInterface $pageLoader
     * @param ProductListingProviderInterface $productListingProvider
     */
    public function __construct(
        MixServiceInterface $mixService,
        SessionInterface $session,
        GenericPageLoaderInterface $pageLoader,
        ProductListingProviderInterface $productListingProvider
    ) {
        $this->mixService = $mixService;
        $this->session = $session;
        $this->pageLoader = $pageLoader;
        $this->productListingProvider = $productListingProvider;
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

        $listingProductCollection = $this->productListingProvider->getDefaultListingProductCollection($salesChannelContext);

        return $this->renderStorefront(
            '@InvMixerProduct/storefront/page/mix.index.html.twig',
            [
                'page' => $this->pageLoader->load($request, $salesChannelContext),
                'listingProductCollection' => $listingProductCollection,
            ]
        );
    }

}
