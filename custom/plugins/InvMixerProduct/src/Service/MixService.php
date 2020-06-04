<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Entity\MixEntity as Subject;
use InvMixerProduct\Entity\MixItemEntity;
use InvMixerProduct\Repository\MixEntityRepository;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class MixSessionService
 * @package InvMixerProduct\Service
 */
class MixService implements MixServiceInterface
{

    /**
     * @var MixEntityRepository
     */
    private $mixRepository;

    /**
     * MixService constructor.
     * @param MixEntityRepository $mixRepository
     */
    public function __construct(MixEntityRepository $mixRepository)
    {
        $this->mixRepository = $mixRepository;
    }

    /**
     * @inheritDoc
     */
    public function addProduct(
        Subject $subject,
        ProductEntity $productEntity,
        SalesChannelContext $context
    ): Subject {
        if ($subject->hasItemOfProduct($productEntity)) {
            $this->setProductQuantity(
                $subject,
                $productEntity,
                $subject->getItemOfProduct($productEntity)->getQuantity() + 1,
                $context
            );
        } else {
            $item = new MixItemEntity();
            $item->setId(Uuid::randomHex());
            $item->setProduct($productEntity);
            $item->setMix($subject);
            $item->setQuantity(1);

            $subject->addMixItem($item);

            $this->save($subject, $context);
        }
        return $subject;
    }

    /**
     * @inheritDoc
     */
    public function setProductQuantity(
        Subject $subject,
        ProductEntity $productEntity,
        int $quantity,
        SalesChannelContext $context
    ): Subject {

        if($quantity <= 0){
            return $this->removeProduct(
                $subject,
                $productEntity,
                $context
            );
        }

        $item = $subject->getItemOfProduct($productEntity);

        $item->setQuantity(
            $quantity
        );

        $this->save($subject, $context);

        return $subject;
    }

    /**
     * @param Subject $subject
     * @param SalesChannelContext $context
     * @return $this
     * @throws \Exception
     */
    private function save(Subject $subject, SalesChannelContext $context): self
    {
        $this->mixRepository->save($subject, $context->getContext());
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeProduct(
        Subject $subject,
        ProductEntity $productEntity,
        SalesChannelContext $context
    ): Subject {

        $item = $subject->getItemOfProduct($productEntity);
        $subject->getItems()->remove($item->getId());
        $this->save($subject, $context);

        return $subject;
    }

    /**
     * @inheritDoc
     */
    public function setLabel(
        Subject $subject,
        string $label,
        SalesChannelContext $context
    ): Subject {
        die(__METHOD__);
    }


}
