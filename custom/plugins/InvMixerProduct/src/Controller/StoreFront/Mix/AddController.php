<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\Exception\EntityNotFoundException;
use InvMixerProduct\Exception\NumberOfProductsExceededException;
use InvMixerProduct\Repository\ProductRepository;
use InvMixerProduct\Service\MixServiceInterface;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AddController
 *
 * @package InvMixerProduct\Controller\StoreFront\Mix
 *
 * @RouteScope(scopes={"storefront"})
 * @Route("/mix/add", methods={"POST"}, defaults={"csrf_protected": false}, name="invMixerProduct.storeFront.mix.session.add")
 */
class AddController extends MixController
{


    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var MixServiceInterface
     */
    private $mixService;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * AddController constructor.
     * @param ProductRepository $productRepository
     * @param SessionInterface $session
     * @param MixServiceInterface $mixService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ProductRepository $productRepository,
        SessionInterface $session,
        MixServiceInterface $mixService,
        TranslatorInterface $translator
    ) {
        $this->productRepository = $productRepository;
        $this->session = $session;
        $this->mixService = $mixService;
        $this->translator = $translator;
    }


    /**
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return RedirectResponse
     *
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    public function __invoke(Request $request, SalesChannelContext $salesChannelContext)
    {

        $productId = $request->get('product_id');

        $product = $this->productRepository->mustFindOneEligibleForMixById(
            $productId,
            $salesChannelContext->getContext()
        );

        $mix = $this->getOrInitiateCurrentMix(
            $salesChannelContext,
            $this->session,
            $this->mixService
        );

        try {
            $this->mixService->addProduct(
                $mix,
                $product,
                $salesChannelContext
            );
        } catch (NumberOfProductsExceededException $e) {
            $this->addFlash(
                'alert',
                $this->trans(
                    $e->getMessageKey(),
                    $e->getParameters()
                )
            );
        } catch (\Throwable $e) {
            $this->addFlash(
                'alert',
                $e->getMessage()
            );
        }

        return $this->redirectToRoute(
            'invMixerProduct.storeFront.mix.state',
            [],
            302
        );

    }
}
