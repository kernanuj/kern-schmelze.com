<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\Service\MixContainerDefinitionProviderInterface;
use InvMixerProduct\Service\MixServiceInterface;
use InvMixerProduct\Service\MixViewTransformer;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
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
 * @Route("/mix/state", methods={"GET"}, name="invMixerProduct.storeFront.mix.state")
 */
class StateController extends MixController
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
     * @var MixViewTransformer
     */
    private $mixViewTransformer;

    /**
     * @var MixContainerDefinitionProviderInterface
     */
    private $mixContainerDefinitionProvider;

    /**
     * @var GenericPageLoaderInterface
     */
    private $pageLoader;

    /**
     * IndexController constructor.
     * @param MixServiceInterface $mixService
     * @param SessionInterface $session
     * @param MixViewTransformer $mixViewTransformer
     * @param MixContainerDefinitionProviderInterface $mixContainerDefinitionProvider
     * @param GenericPageLoaderInterface $pageLoader
     */
    public function __construct(
        MixServiceInterface $mixService,
        SessionInterface $session,
        MixViewTransformer $mixViewTransformer,
        MixContainerDefinitionProviderInterface $mixContainerDefinitionProvider,
        GenericPageLoaderInterface $pageLoader
    ) {
        $this->mixService = $mixService;
        $this->session = $session;
        $this->mixViewTransformer = $mixViewTransformer;
        $this->mixContainerDefinitionProvider = $mixContainerDefinitionProvider;
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

        $mixView = $this->getOrInitiateCurrentMixAndReturnAsView(
            $this->mixViewTransformer,
            $salesChannelContext,
            $this->session,
            $this->mixService
        );

        return $this->renderStorefront(
            '@InvMixerProduct/storefront/component/mix.state.html.twig',
            [
                'page' => $this->pageLoader->load($request, $salesChannelContext),
                'containerDefinitionCollection' => $this->mixContainerDefinitionProvider->getAvailableContainerCollection(),
                'mixView' => $mixView,
            ]
        );
    }
}
