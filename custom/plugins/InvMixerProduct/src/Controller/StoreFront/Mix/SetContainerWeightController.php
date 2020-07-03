<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\Exception\EntityNotFoundException;
use InvMixerProduct\Service\MixContainerDefinitionProviderInterface;
use InvMixerProduct\Service\MixServiceInterface;
use InvMixerProduct\Value\Weight;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SetContainerWeightController
 *
 * @package InvMixerProduct\Controller\StoreFront\Mix
 *
 * @RouteScope(scopes={"storefront"})
 * @Route("/mix/container/weight", methods={"POST"}, name="invMixerProduct.storeFront.mix.session.container.weight.set")
 */
class SetContainerWeightController extends MixController
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
     *
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

        $containerDefinition = $containerDefinitionCollection->oneOfWeightDesignAndBaseProduct(
            Weight::xGrams((int)$request->get('weight')),
            $mix->getContainerDefinition()->getDesign(),
            $mix->getContainerDefinition()->getBaseProduct()
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
