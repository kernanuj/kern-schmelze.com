<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\Constants;
use InvMixerProduct\Exception\EntityNotFoundException;
use InvMixerProduct\Service\MixServiceInterface;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class SetLabelController
 *
 * @package InvMixerProduct\Controller\StoreFront\Mix
 *
 * @RouteScope(scopes={"storefront"})
 * @Route("/mix/label", methods={"POST"}, defaults={"csrf_protected": false}, name="invMixerProduct.storeFront.mix.session.label.set")
 */
class SetLabelController extends MixController
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
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * SetLabelController constructor.
     * @param SessionInterface $session
     * @param MixServiceInterface $mixService
     * @param ValidatorInterface $validator
     */
    public function __construct(
        SessionInterface $session,
        MixServiceInterface $mixService,
        ValidatorInterface $validator
    ) {
        $this->session = $session;
        $this->mixService = $mixService;
        $this->validator = $validator;
    }


    /**
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return RedirectResponse|Response
     * @throws EntityNotFoundException
     */
    public function __invoke(Request $request, SalesChannelContext $salesChannelContext)
    {
        $label = $request->get('label');

        $validationErrors = $this->validator->validate(
            $label,
            [
                new Constraints\NotNull(),
                new Constraints\Length(
                    [
                        'max' => 125,
                        'min' => 3
                    ]
                ),
                new Constraints\Regex(
                    [
                        'pattern' => Constants::LABEL_REGEX_PATTERN
                    ]
                )
            ]
        );

        if (count($validationErrors) > 0) {
            $this->addFlash(
                'error',
                (string)$validationErrors
            );

            return $this->redirectToRoute(
                'invMixerProduct.storeFront.mix.state',
                [],
                302
            );
        }

        $mix = $this->getOrInitiateCurrentMix(
            $salesChannelContext,
            $this->session,
            $this->mixService
        );

        try {
            $this->mixService->setLabel(
                $mix,
                $label,
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
