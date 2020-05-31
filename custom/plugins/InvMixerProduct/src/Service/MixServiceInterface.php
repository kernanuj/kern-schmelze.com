<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Entity\MixEntity as Subject;
use Shopware\Core\Content\Product\ProductEntity;

/**
 * Interface MixSessionServiceInterface
 * @package InvMixerProduct\Service
 */
interface MixServiceInterface
{

    /**
     * @param Subject $mixEntity
     * @param ProductEntity $productEntity
     * @return Subject
     */
    public function addProduct(Subject $mixEntity, ProductEntity $productEntity): Subject;

    /**
     * @param Subject $mixEntity
     * @param ProductEntity $productEntity
     * @return Subject
     */
    public function removeProduct(Subject $mixEntity, ProductEntity $productEntity): Subject;

    /**
     * @param Subject $mixEntity
     * @param ProductEntity $productEntity
     * @param int $quantity
     * @return Subject
     */
    public function setProductQuantity(Subject $mixEntity, ProductEntity $productEntity, int $quantity): Subject;

    /**
     * @param Subject $mixEntity
     * @param string $label
     * @return Subject
     */
    public function setLabel(Subject $mixEntity, string $label): Subject;

}
