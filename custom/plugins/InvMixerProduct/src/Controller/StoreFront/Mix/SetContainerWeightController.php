<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\Exception\EntityNotFoundException;
use InvMixerProduct\Exception\NumberOfProductsExceededException;
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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SetContainerWeightController
 *
 * @package InvMixerProduct\Controller\StoreFront\Mix
 *
 * @RouteScope(scopes={"storefront"})
 * @Route("/mix/container/weight", methods={"POST"}, defaults={"csrf_protected": false}, name="invMixerProduct.storeFront.mix.session.container.weight.set")
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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * SetContainerWeightController constructor.
     * @param SessionInterface $session
     * @param MixServiceInterface $mixService
     * @param MixContainerDefinitionProviderInterface $containerDefinitionProvider
     * @param TranslatorInterface $translator
     */
    public function __construct(
        SessionInterface $session,
        MixServiceInterface $mixService,
        MixContainerDefinitionProviderInterface $containerDefinitionProvider,
        TranslatorInterface $translator
    ) {
        $this->session = $session;
        $this->mixService = $mixService;
        $this->containerDefinitionProvider = $containerDefinitionProvider;
        $this->translator = $translator;
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
        } catch (NumberOfProductsExceededException $e) {
            $this->addFlash(
                'alert',
                $this->trans(
                    $e->getMessageKey() ,
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
