<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Entity\MixEntity as Subject;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Interface MixToCartItemConverterInterface
 * @package InvMixerProduct\Service
 */
interface MixToCartItemConverterInterface
{

    /**
     * @param Subject $subject
     * @param SalesChannelContext $salesChannelContext
     *
     * @return LineItem
     */
    public function toCartItem(
        Subject $subject,
        SalesChannelContext $salesChannelContext
    ): LineItem;

}
