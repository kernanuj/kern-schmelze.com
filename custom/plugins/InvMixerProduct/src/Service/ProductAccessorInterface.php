<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Value\Price;
use InvMixerProduct\Value\Weight;
use Shopware\Core\Content\Product\ProductEntity as Subject;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Interface ProductAccessorInterface
 * @package InvMixerProduct\Service
 */
interface ProductAccessorInterface
{

    /**
     * @param Subject $subject
     * @param SalesChannelContext $context
     * @return bool
     */
    public function isEligibleProduct(
        Subject $subject,
        SalesChannelContext $context
    ): bool;

    /**
     * @param Subject $subject
     * @param SalesChannelContext $context
     * @return int
     */
    public function accessProductAvailableStock(
        Subject $subject,
        SalesChannelContext $context
    ): int;

    /**
     * @param Subject $subject
     * @param SalesChannelContext $context
     * @return Weight
     */
    public function accessProductWeight(
        Subject $subject,
        SalesChannelContext $context
    ): Weight;

    /**
     * @param Subject $subject
     * @param SalesChannelContext $context
     * @return Price
     */
    public function accessProductPrice(
        Subject $subject,
        SalesChannelContext $context
    ): Price;


}
