<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\Exception\EntityNotFoundException;
use InvMixerProduct\Service\MixContainerDefinitionProviderInterface;
use InvMixerProduct\Service\MixServiceInterface;
use InvMixerProduct\Value\Design;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SetContainerDesignController
 *
 * @package InvMixerProduct\Controller\StoreFront\Mix
 *
 * @RouteScope(scopes={"storefront"})
 * @Route("/mix/container/design", methods={"POST"}, name="invMixerProduct.storeFront.mix.session.container.design.set")
 */
class SetContainerDesignController extends MixController
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
     * @var MixContainerDefinitionProviderInterface
     */
    private $containerDefinitionProvider;

    /**
     * SetContainerDesignController constructor.
     * @param SessionInterface $session
     * @param MixServiceInterface $mixService
     * @param MixContainerDefinitionProviderInterface $containerDefinitionProvider
     */
    public function __construct(
        SessionInterface $session,
        MixServiceInterface $mixService,
        MixContainerDefinitionProviderInterface $containerDefinitionProvider
    ) {
        $this->session = $session;
        $this->mixService = $mixService;
        $this->containerDefinitionProvider = $containerDefinitionProvider;
    }


    /**
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return RedirectResponse|Response
     * @throws EntityNotFoundException
     * @throws \Exception
     */
    public function __invoke(Request $request, SalesChannelContext $salesChannelContext)
    {

        $containerDefinitionCollection = $this->containerDefinitionProvider->getAvailableContainerCollection();

        $mix = $this->getOrInitiateCurrentMix(
            $salesChannelContext,
            $this->session,
            $this->mixService
        );

        $containerDefinition = $containerDefinitionCollection->oneOfWeightAndDesign(
            $mix->getContainerDefinition()->getMaxContainerWeight(),
            Design::fromString($request->get('design'))
        );
        try {
            $this->mixService->applyContainerDefinition(
                $mix,
                $containerDefinition,
                $salesChannelContext
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
