<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ResetController
 *
 * @package InvMixerProduct\Controller\StoreFront\Mix
 *
 * @RouteScope(scopes={"storefront"})
 * @Route("/mix/reset", methods={"GET"}, name="invMixerProduct.storeFront.mix.session.reset")
 */
class ResetController extends MixController
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * ResetController constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return Response
     */
    public function __invoke(SalesChannelContext $salesChannelContext)
    {
        $this->session->remove(self::SESSION_KEY_CURRENT_MIX);

        return RedirectResponse::create(
            $this->generateUrl(
                'invMixerProduct.storeFront.mix.index'
            ),
            301
        );

    }
}
