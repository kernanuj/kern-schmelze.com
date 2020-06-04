<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Entity\MixEntity as Subject;
use InvMixerProduct\Exception\EntityNotFoundException;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Interface MixSessionServiceInterface
 * @package InvMixerProduct\Service
 */
interface MixServiceInterface
{

    /**
     * @param Subject $subject
     * @param ProductEntity $productEntity
     * @param SalesChannelContext $context
     * @return Subject
     *
     * @throws EntityNotFoundException
     */
    public function addProduct(
        Subject $subject,
        ProductEntity $productEntity,
        SalesChannelContext $context
    ): Subject;

    /**
     * @param Subject $subject
     * @param ProductEntity $productEntity
     * @param SalesChannelContext $context
     * @return Subject
     *
     * @throws EntityNotFoundException
     */
    public function removeProduct(
        Subject $subject,
        ProductEntity $productEntity,
        SalesChannelContext $context
    ): Subject;

    /**
     * @param Subject $subject
     * @param ProductEntity $productEntity
     * @param int $quantity
     * @param SalesChannelContext $context
     * @return Subject
     *
     * @throws EntityNotFoundException
     */
    public function setProductQuantity(
        Subject $subject,
        ProductEntity $productEntity,
        int $quantity,
        SalesChannelContext $context
    ): Subject;

    /**
     * @param Subject $mixEntity
     * @param string $label
     * @param SalesChannelContext $context
     * @return Subject
     *
     * @throws EntityNotFoundException
     */
    public function setLabel(
        Subject $mixEntity,
        string $label,
        SalesChannelContext $context
    ): Subject;

}
