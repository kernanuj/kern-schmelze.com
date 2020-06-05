<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Entity\MixEntity as Subject;
use InvMixerProduct\Exception\EntityNotFoundException;
use InvMixerProduct\Struct\ContainerDefinition;
use Shopware\Core\Checkout\Customer\CustomerEntity;
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
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
     */
    public function setProductQuantity(
        Subject $subject,
        ProductEntity $productEntity,
        int $quantity,
        SalesChannelContext $context
    ): Subject;

    /**
     * @param Subject $mixEntity
     * @param ContainerDefinition $containerDefinition
     * @param SalesChannelContext $context
     *
     * @return Subject
     *
     * @throws \Exception
     */
    public function applyContainerDefinition(
        Subject $mixEntity,
        ContainerDefinition $containerDefinition,
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

    /**
     * @param SalesChannelContext $context
     * @return Subject
     *
     * @throws \Exception
     */
    public function create(
        SalesChannelContext $context
    ): Subject;

    /**
     * @param string $id
     * @param SalesChannelContext $context
     * @return Subject
     *
     * @throws \Exception
     */
    public function read(
        string $id,
        SalesChannelContext $context
    ): Subject;

    /**
     * @param Subject $subject
     * @param CustomerEntity $customerEntity
     * @param SalesChannelContext $salesChannelContext
     * @return Subject
     *
     * @throws \Exception
     */
    public function assignCustomer(
        Subject $subject,
        CustomerEntity $customerEntity,
        SalesChannelContext $salesChannelContext
    ): Subject;

}
