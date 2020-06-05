<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\Exception\EntityNotFoundException;
use InvMixerProduct\Repository\ProductRepository;
use InvMixerProduct\Service\MixServiceInterface;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AddController
 *
 * @package InvMixerProduct\Controller\StoreFront\Mix
 *
 * @RouteScope(scopes={"storefront"})
 * @Route("/mix/add", methods={"POST"}, name="invMixerProduct.storeFront.mix.session.add")
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
     * AddController constructor.
     * @param ProductRepository $productRepository
     * @param SessionInterface $session
     * @param MixServiceInterface $mixService
     */
    public function __construct(
        ProductRepository $productRepository,
        SessionInterface $session,
        MixServiceInterface $mixService
    ) {
        $this->productRepository = $productRepository;
        $this->session = $session;
        $this->mixService = $mixService;
    }


    /**
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return RedirectResponse
     *
     * @throws EntityNotFoundException
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

        $this->mixService->addProduct(
            $mix,
            $product,
            $salesChannelContext
        );

        return RedirectResponse::create(
            $this->generateUrl(
                'invMixerProduct.storeFront.mix.index'
            ),
            301
        );

    }
}
