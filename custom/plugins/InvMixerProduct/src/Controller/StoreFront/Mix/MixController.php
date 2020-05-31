<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\DataObject\MixView;
use InvMixerProduct\Entity\MixEntity;
use InvMixerProduct\Repository\MixEntityRepository;
use InvMixerProduct\Service\MixViewTransformer;
use Shopware\Core\Framework\Context;
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
     * @param Context $context
     * @param SessionInterface $session
     * @return MixView
     */
    protected function getOrInitiateCurrentMixAndReturnAsView(
        MixViewTransformer $mixViewTransformer,
        SalesChannelContext $salesChannelContext,
        SessionInterface $session
    ): MixView {

        $mixEntity = $this->getOrInitiateCurrentMix(
            $salesChannelContext,
            $session
        );

        return $mixViewTransformer->transform(
            $salesChannelContext,
            $mixEntity
        );
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @param SessionInterface $session
     * @return MixEntity
     */
    protected function getOrInitiateCurrentMix(
        SalesChannelContext $salesChannelContext,
        SessionInterface $session
    ): MixEntity {

        $repository = $this->container->get(MixEntityRepository::class);

        $currentMixId = $session->get(self::SESSION_KEY_CURRENT_MIX);

        if (!$currentMixId) {
            $entity = $repository->create();
            $repository->save($entity, $salesChannelContext->getContext());
            $session->set(self::SESSION_KEY_CURRENT_MIX, $entity->getId());
        } else {
            $entity = $repository->findOneById($currentMixId, $salesChannelContext->getContext());
        }

        if($salesChannelContext->getCustomer()){
            if(!$entity->getCustomer()){
                $entity->setCustomer($salesChannelContext->getCustomer());
                $repository->save($entity, $salesChannelContext->getContext());
            }
        }

        return $entity;
    }
}
