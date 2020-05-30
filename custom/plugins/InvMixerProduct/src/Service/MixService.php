<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Entity\Mix as Subject;
use Shopware\Core\Content\Product\ProductEntity;

/**
 * Class MixSessionService
 * @package InvMixerProduct\Service
 */
class MixService implements MixServiceInterface
{

    /**
     * @inheritDoc
     */
    public function addProduct(Subject $subject, ProductEntity $productEntity): Subject
    {
        die(__METHOD__);
    }

    /**
     * @inheritDoc
     */
    public function removeProduct(Subject $subject, ProductEntity $productEntity): Subject
    {
        die(__METHOD__);
    }

    /**
     * @inheritDoc
     */
    public function setProductQuantity(Subject $subject, ProductEntity $productEntity, int $quantity): Subject
    {
        die(__METHOD__);
    }


    public function setLabel(Subject $subject, string $label): Subject
    {
        die(__METHOD__);
    }


}
