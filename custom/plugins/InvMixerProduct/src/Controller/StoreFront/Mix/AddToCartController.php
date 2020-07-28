<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use Exception;
use InvMixerProduct\Exception\InsufficientMixComponentsException;
use InvMixerProduct\Service\MixServiceInterface;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AddToCartController
 *
 * @package InvMixerProduct\Controller\StoreFront\Mix
 *
 * @RouteScope(scopes={"storefront"})
 * @Route("/mix/to-cart", methods={"POST"}, defaults={"csrf_protected": false}, name="invMixerProduct.storeFront.mix.session.addToCart")
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
     * @var CartService
     */
    private $cartService;

    /**
     * AddToCartController constructor.
     * @param SessionInterface $session
     * @param MixServiceInterface $mixService
     * @param CartService $cartService
     */
    public function __construct(SessionInterface $session, MixServiceInterface $mixService, CartService $cartService)
    {
        $this->session = $session;
        $this->mixService = $mixService;
        $this->cartService = $cartService;
    }

    /**
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return RedirectResponse
     * @throws Exception
     */
    public function __invoke(Request $request, SalesChannelContext $salesChannelContext)
    {
        $mix = $this->getOrInitiateCurrentMix(
            $salesChannelContext,
            $this->session,
            $this->mixService
        );

        $quantity = (int)$request->get('quantity', 1);

        try {
            $cartLineItem = $this->mixService->convertToCartItem(
                $mix,
                $quantity,
                $salesChannelContext
            );
        } catch (InsufficientMixComponentsException $exception) {
            $this->addFlash(
                'alert',
                $this->trans('invMixerProduct.mix.addToCart.failed.insufficientComponents', ['%count%' => $mix->getCountOfDifferentProducts()])
            );

            return $this->redirectToRoute(
                'invMixerProduct.storeFront.mix.index'
            );
        }

        $cart = $this->cartService->getCart(
            $salesChannelContext->getToken(),
            $salesChannelContext
        );

        $this->cartService->add(
            $cart,
            $cartLineItem,
            $salesChannelContext
        );

        $this->removeFromSession($this->session);


        $this->addFlash(
            'success',
            $this->trans('checkout.addToCartSuccess', ['%count%' => $quantity])
        );

        return $this->redirectToRoute(
            'frontend.checkout.cart.page'
        );
    }
}
