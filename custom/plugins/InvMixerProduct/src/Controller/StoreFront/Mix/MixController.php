<?php declare(strict_types=1);

namespace InvMixerProduct\Controller\StoreFront\Mix;

use InvMixerProduct\DataObject\MixView;
use InvMixerProduct\Entity\Mix;
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

    /**
     * @param MixViewTransformer $mixViewTransformer
     * @param SalesChannelContext $salesChannelContext
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
     * @return Mix
     */
    protected function getOrInitiateCurrentMix(
        SalesChannelContext $salesChannelContext,
        SessionInterface $session): Mix
    {

        $session->set('inv.mix', 123);
        echo $session->get('inv.mix');
        return new Mix();
    }
}
