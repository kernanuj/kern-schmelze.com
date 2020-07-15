<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\Service\MixContainerDefinitionProviderInterface;
use InvMixerProduct\Service\MixServiceInterface;
use InvMixerProduct\Service\MixViewTransformer;
use InvMixerProduct\Value\Weight;
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
 * @Route("/mix/init", methods={"GET"}, name="invMixerProduct.storeFront.mix.init")
 * @todo: exclude route from seo
 * @todo: disable csrf
 */
class InitController extends MixController
{

    /**
     * @var MixServiceInterface
     */
    private $mixService;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var MixContainerDefinitionProviderInterface
     */
    private $mixContainerDefinitionProvider;

    /**
     * InitiController constructor.
     * @param MixServiceInterface $mixService
     * @param SessionInterface $session
     * @param MixContainerDefinitionProviderInterface $mixContainerDefinitionProvider
     */
    public function __construct(
        MixServiceInterface $mixService,
        SessionInterface $session,
        MixContainerDefinitionProviderInterface $mixContainerDefinitionProvider
    ) {
        $this->mixService = $mixService;
        $this->session = $session;
        $this->mixContainerDefinitionProvider = $mixContainerDefinitionProvider;
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

        $containerDefinitionCollection = $this->mixContainerDefinitionProvider->getAvailableContainerCollection();

        $this->removeFromSession($this->session);

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
            'invMixerProduct.storeFront.mix.index',
            [],
            302
        );


    }
}
