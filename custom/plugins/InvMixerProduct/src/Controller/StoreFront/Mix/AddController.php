<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class IndexController
 *
 * @package InvMixerProduct\Controller\StoreFront\Mix
 *
 * @RouteScope(scopes={"storefront"})
 * @Route("/mix/add", methods={"GET"}, name="invMixerProduct.storeFront.mix.session.add")
 */
class AddController extends MixController
{


    /**
     * @param SalesChannelContext $salesChannelContext
     * @return Response
     */
    public function __invoke(SalesChannelContext $salesChannelContext)
    {
        return RedirectResponse::create(
            $this->generateUrl(
                'invMixerProduct.storeFront.mix.index'
            ),
            301
        );

    }
}
