<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\Service\MixServiceInterface;
use InvMixerProduct\Service\ProductListingProviderInterface;
use InvMixerProduct\Storefront\Page\IndexPageLoader;
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
     * @var IndexPageLoader
     */
    private $pageLoader;

    /**
     * IndexController constructor.
     * @param IndexPageLoader $pageLoader
     */
    public function __construct(IndexPageLoader $pageLoader)
    {
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

        $page = $this->pageLoader->load($request, $salesChannelContext);

        return $this->renderStorefront(
            '@InvMixerProduct/storefront/page/mix.index.html.twig',
            [
                'page' => $page,
                'listingProductCollection' => $page->getProductCollection()
            ]
        );
    }

}
