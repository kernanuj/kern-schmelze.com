<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use Exception;
use InvMixerProduct\Entity\MixEntity as Subject;
use InvMixerProduct\Entity\MixItemEntity;
use InvMixerProduct\Exception\ContainerWeightExceededException;
use InvMixerProduct\Exception\NotEligibleProductException;
use InvMixerProduct\Exception\NumberOfProductsExceededException;
use InvMixerProduct\Exception\ProductStockExceededException;
use InvMixerProduct\Repository\MixEntityRepository;
use InvMixerProduct\Struct\ContainerDefinition;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Customer\CustomerEntity;
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
     * @var ProductAccessorInterface
     */
    private $productAccessor;

    /**
     * @var MixToCartItemConverterInterface
     */
    private $mixToCartItemConverter;

    /**
     * MixService constructor.
     * @param MixEntityRepository $mixRepository
     * @param ProductAccessorInterface $productAccessor
     * @param MixToCartItemConverterInterface $mixToCartItemConverter
     */
    public function __construct(
        MixEntityRepository $mixRepository,
        ProductAccessorInterface $productAccessor,
        MixToCartItemConverterInterface $mixToCartItemConverter
    ) {
        $this->mixRepository = $mixRepository;
        $this->productAccessor = $productAccessor;
        $this->mixToCartItemConverter = $mixToCartItemConverter;
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
            $this->assertCanAddProduct(
                $subject,
                $productEntity,
                $context
            );
            $item = new MixItemEntity();
            $item->setId(Uuid::randomHex());
            $item->setProduct($productEntity);
            $item->setMix($subject);
            $item->setQuantity(1);

            $subject->addMixItem($item);

            $this->assertCanSetItemQuantity(
                $subject,
                $item,
                1,
                $context
            );

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

        if ($quantity <= 0) {
            return $this->removeProduct(
                $subject,
                $productEntity,
                $context
            );
        }

        $item = $subject->getItemOfProduct($productEntity);

        $this->assertCanSetItemQuantity(
            $subject,
            $item,
            $quantity,
            $context
        );

        $item->setQuantity(
            $quantity
        );

        $this->save($subject, $context);

        return $subject;
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
     * @param Subject $subject
     * @param SalesChannelContext $context
     * @return Subject
     * @throws Exception
     */
    public function save(
        Subject $subject,
        SalesChannelContext $context
    ): Subject {
        $this->mixRepository->save($subject, $context->getContext());
        return $subject;
    }

    /**
     * @param Subject $subject
     * @param MixItemEntity $mixItem
     * @param int $quantity
     * @param SalesChannelContext $context
     * @throws ContainerWeightExceededException
     * @throws NumberOfProductsExceededException
     * @throws ProductStockExceededException
     */
    private function assertCanSetItemQuantity(
        Subject $subject,
        MixItemEntity $mixItem,
        int $quantity,
        SalesChannelContext $context
    ): void {
        $itemQuantityDifference = $quantity - $mixItem->getQuantity();

        $itemWeight = $this->productAccessor->accessProductWeight($mixItem->getProduct(), $context);
        $totalMixWeight = $subject->getTotalWeight(
            $this->productAccessor,
            $context
        );

        $maxAllowedWeight = $subject->getContainerDefinition()->getFillDelimiter()->getWeight();
        $newWeight = $totalMixWeight->add($itemWeight->multipliedBy($itemQuantityDifference));
        if ($newWeight->isGreaterThan($maxAllowedWeight)) {
            throw ContainerWeightExceededException::fromContainerAndWeight(
                $subject->getContainerDefinition(),
                $newWeight
            );
        }

        $maxAllowedProducts = $subject->getContainerDefinition()->getFillDelimiter()->getAmount()->getValue();
        $currentProducts = $subject->getTotalItemQuantity();

        if ($currentProducts + $itemQuantityDifference > $maxAllowedProducts) {
            throw NumberOfProductsExceededException::fromCountAndContainerDefinition(
                $subject->getContainerDefinition(),
                $currentProducts + $itemQuantityDifference
            );
        }


        $availableStock = $this->productAccessor->accessProductAvailableStock($mixItem->getProduct(), $context);
        if ($availableStock < $quantity + $mixItem->getQuantity()) {
            throw ProductStockExceededException::fromProductAndRequestedStock(
                $mixItem->getProduct(),
                $availableStock,
                $quantity + $mixItem->getQuantity()
            );
        }
    }

    /**
     * @param Subject $subject
     * @param ProductEntity $productEntity
     * @param SalesChannelContext $context
     *
     * @throws NotEligibleProductException
     * @throws NumberOfProductsExceededException
     */
    private function assertCanAddProduct(
        Subject $subject,
        ProductEntity $productEntity,
        SalesChannelContext $context
    ): void {
        if (true !== $this->productAccessor->isEligibleProduct($productEntity, $context)) {
            throw NotEligibleProductException::fromProductEntity($productEntity);
        }

        if ($subject->getCountOfDifferentProducts() > $subject->getContainerDefinition()->getFillDelimiter()->getAmount()->getValue()) {
            throw NumberOfProductsExceededException::fromCountAndContainerDefinition(
                $subject->getContainerDefinition(),
                $subject->getCountOfDifferentProducts() + 1
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function create(
        SalesChannelContext $context
    ): Subject {

        $entity = $this->mixRepository->create();
        return $this->save($entity, $context);
    }

    /**
     * @inheritDoc
     */
    public function read(
        string $id,
        SalesChannelContext $context
    ): Subject {
        return $this->mixRepository->findOneById(
            $id,
            $context->getContext()
        );
    }

    /**
     * @inheritDoc
     */
    public function assignCustomer(
        Subject $subject,
        CustomerEntity $customerEntity,
        SalesChannelContext $salesChannelContext
    ): Subject {

        $subject->setCustomer($customerEntity);

        return $this->save($subject, $salesChannelContext);
    }

    /**
     * @inheritDoc
     */
    public function applyContainerDefinition(
        Subject $subject,
        ContainerDefinition $containerDefinition,
        SalesChannelContext $context
    ): Subject {

        $this->assertCanApplyContainerDefinition(
            $subject,
            $containerDefinition,
            $context
        );

        $subject->setContainerDefinition($containerDefinition);
        return $this->save($subject, $context);
    }

    /**
     * @param Subject $subject
     * @param ContainerDefinition $containerDefinition
     * @param SalesChannelContext $context
     * @throws ContainerWeightExceededException
     * @throws NumberOfProductsExceededException
     */
    private function assertCanApplyContainerDefinition(
        Subject $subject,
        ContainerDefinition $containerDefinition,
        SalesChannelContext $context
    ): void {


        $currentWeight = $subject->getTotalWeight(
            $this->productAccessor,
            $context
        );
        if ($currentWeight->isGreaterThan($containerDefinition->getFillDelimiter()->getWeight())) {
            throw ContainerWeightExceededException::fromContainerAndWeight(
                $containerDefinition,
                $currentWeight
            );
        }

        $currentProductCount = $subject->getTotalItemQuantity();
        if ($currentProductCount > $containerDefinition->getFillDelimiter()->getAmount()->getValue()) {
            throw NumberOfProductsExceededException::fromCountAndContainerDefinition(
                $containerDefinition,
                $currentProductCount
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function setLabel(
        Subject $subject,
        string $label,
        SalesChannelContext $salesChannelContext
    ): Subject {
        $subject->setLabel($label);
        $this->save($subject, $salesChannelContext);

        return $subject;
    }

    /**
     * @inheritDoc
     */
    public function convertToCartItem(
        Subject $subject,
        int $quantity,
        SalesChannelContext $salesChannelContext
    ): LineItem {
        return $this->mixToCartItemConverter->toCartItem(
            $subject,
            $quantity,
            $salesChannelContext
        );
    }


}
