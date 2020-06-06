<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use Exception;
use InvMixerProduct\Service\MixServiceInterface;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AddToCartController
 *
 * @package InvMixerProduct\Controller\StoreFront\Mix
 *
 * @RouteScope(scopes={"storefront"})
 * @Route("/mix/to-cart", methods={"POST"}, name="invMixerProduct.storeFront.mix.session.addToCart")
 */
class AddToCartController extends MixController
{

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var MixServiceInterface
     */
    private $mixService;

    /**
     * AddController constructor.
     * @param SessionInterface $session
     * @param MixServiceInterface $mixService
     */
    public function __construct(
        SessionInterface $session,
        MixServiceInterface $mixService
    ) {
        $this->session = $session;
        $this->mixService = $mixService;
    }


    /**
     * @param SalesChannelContext $salesChannelContext
     * @return RedirectResponse
     * @throws Exception
     */
    public function __invoke(SalesChannelContext $salesChannelContext)
    {

        $mix = $this->getOrInitiateCurrentMix(
            $salesChannelContext,
            $this->session,
            $this->mixService
        );

        $this->mixService->addToCart(
            $mix,
            $salesChannelContext
        );

        $this->session->remove(
            self::SESSION_KEY_CURRENT_MIX
        );

        $this->addFlash(
            'success',
            'product has been added to cart'
        );

        return $this->redirectToRoute(
            'frontend.checkout.cart.page'
        );
    }
}
