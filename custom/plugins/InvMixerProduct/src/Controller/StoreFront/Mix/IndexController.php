<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\Service\MixServiceInterface;
use InvMixerProduct\Service\MixViewTransformer;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
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
     * @var MixViewTransformer
     */
    private $mixViewTransformer;

    /**
     * IndexController constructor.
     * @param ProductListingLoader $productListingLoader
     * @param MixServiceInterface $mixSessionService
     * @param SessionInterface $session
     * @param MixViewTransformer $mixViewTransformer
     */
    public function __construct(
        ProductListingLoader $productListingLoader,
        MixServiceInterface $mixSessionService,
        SessionInterface $session,
        MixViewTransformer $mixViewTransformer
    ) {
        $this->productListingLoader = $productListingLoader;
        $this->mixService = $mixSessionService;
        $this->session = $session;
        $this->mixViewTransformer = $mixViewTransformer;
    }


    /**
     * @param SalesChannelContext $salesChannelContext
     * @param Context $context
     * @return Response
     */
    public function __invoke(SalesChannelContext $salesChannelContext, Context $context)
    {

        $mixView = $this->getOrInitiateCurrentMixAndReturnAsView(
            $this->mixViewTransformer,
            $salesChannelContext,
            $this->session,
            $this->mixService
        );

        $productListing = $this->productListingLoader->load(
            new Criteria(),
            $salesChannelContext
        );

        return $this->renderStorefront(
            '@InvMixerProduct/storefront/page/mix.index.html.twig',
            [
                'mixView' => $mixView,
                'productListing' => $productListing
            ]
        );
    }
}
