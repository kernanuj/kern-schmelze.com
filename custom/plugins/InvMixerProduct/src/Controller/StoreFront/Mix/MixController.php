<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\DataObject\MixView;
use InvMixerProduct\Entity\MixEntity;
use InvMixerProduct\Service\MixServiceInterface;
use InvMixerProduct\Service\MixViewTransformer;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class IndexController
 *
 * @package InvMixerProduct\Controller\StoreFront\Mix
 *
 */
abstract class MixController extends StorefrontController
{

    public const SESSION_KEY_CURRENT_MIX = 'invMixerProduct_currentMix';

    /**
     * @param MixViewTransformer $mixViewTransformer
     * @param SalesChannelContext $salesChannelContext
     * @param SessionInterface $session
     * @param MixServiceInterface $mixService
     * @return MixView
     *
     * @throws \Exception
     */
    protected function getOrInitiateCurrentMixAndReturnAsView(
        MixViewTransformer $mixViewTransformer,
        SalesChannelContext $salesChannelContext,
        SessionInterface $session,
        MixServiceInterface $mixService
    ): MixView {

        $mixEntity = $this->getOrInitiateCurrentMix(
            $salesChannelContext,
            $session,
            $mixService
        );

        return $mixViewTransformer->transform(
            $salesChannelContext,
            $mixEntity
        );
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param SessionInterface $session
     * @param MixServiceInterface $mixService
     *
     * @return MixEntity
     *
     * @throws \Exception
     */
    protected function getOrInitiateCurrentMix(
        SalesChannelContext $salesChannelContext,
        SessionInterface $session,
        MixServiceInterface $mixService
    ): MixEntity {


        $currentMixId = $session->get(self::SESSION_KEY_CURRENT_MIX);

        if (!$currentMixId) {
            $entity = $mixService->create($salesChannelContext);
            $session->set(self::SESSION_KEY_CURRENT_MIX, $entity->getId());
        } else {
            $entity = $mixService->read($currentMixId, $salesChannelContext);
        }

        if ($salesChannelContext->getCustomer()) {
            if (!$entity->getCustomer()) {
                $mixService->assignCustomer(
                    $entity,
                    $salesChannelContext->getCustomer(),
                    $salesChannelContext);
            }
        }

        if (!$salesChannelContext->getCustomer()) {
            if ($entity->getCustomer()) {
                $session->remove(self::SESSION_KEY_CURRENT_MIX);
                return $this->getOrInitiateCurrentMix(
                    $salesChannelContext,
                    $session,
                    $mixService
                );
            }
        }

        return $entity;
    }
}
