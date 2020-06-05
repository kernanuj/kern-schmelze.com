<?php declare(strict_types=1);

namespace InvMixerProduct\Entity;

use InvMixerProduct\Exception\EntityNotFoundException;
use InvMixerProduct\Service\ProductAccessorInterface;
use InvMixerProduct\Struct\ContainerDefinition;
use InvMixerProduct\Value\Weight;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class MixEntity
 * @package InvMixerProduct\Entity
 */
class MixEntity extends Entity
{

    use EntityIdTrait;

    /**
     * @var string|null
     */
    protected $label;

    /**
     * @var ContainerDefinition
     */
    protected $containerDefinition;

    /**
     * @var CustomerEntity|null
     */
    protected $customer;

    /**
     * @var string
     */
    protected $customerId;

    /**
     * @var MixItemEntityCollection|null
     */
    protected $items;

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     * @return MixEntity
     */
    public function setLabel(?string $label): MixEntity
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return ContainerDefinition
     */
    public function getContainerDefinition(): ContainerDefinition
    {
        return $this->containerDefinition;
    }

    /**
     * @param ContainerDefinition $containerDefinition
     * @return MixEntity
     */
    public function setContainerDefinition(ContainerDefinition $containerDefinition): MixEntity
    {
        $this->containerDefinition = $containerDefinition;
        return $this;
    }

    /**
     * @return CustomerEntity|null
     */
    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    /**
     * @param CustomerEntity|null $customer
     * @return MixEntity
     */
    public function setCustomer(?CustomerEntity $customer): MixEntity
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * @param string $customerId
     * @return MixEntity
     */
    public function setCustomerId(string $customerId): MixEntity
    {
        $this->customerId = $customerId;
        return $this;
    }

    /**
     * @return MixItemEntityCollection|null
     */
    public function getItems(): ?MixItemEntityCollection
    {
        return $this->items;
    }

    /**
     * @return bool
     */
    public function hasItems(): bool
    {
        if (null == $this->items) {
            return false;
        }

        return $this->items->count() > 0;
    }

    /**
     * @param MixItemEntity $mixItemEntity
     * @return $this
     */
    public function addMixItem(MixItemEntity $mixItemEntity): self
    {

        if (null === $this->items) {
            $this->items = new MixItemEntityCollection();
        }

        $this->items->add($mixItemEntity);

        return $this;
    }

    /**
     * @param ProductEntity $productEntity
     * @return bool
     */
    public function hasItemOfProduct(ProductEntity $productEntity): bool
    {
        try {
            $this->getItemOfProduct($productEntity);
            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }

    /**
     * @param ProductEntity $productEntity
     * @return MixItemEntity
     * @throws EntityNotFoundException
     */
    public function getItemOfProduct(ProductEntity $productEntity): MixItemEntity
    {
        if (null === $this->items) {
            throw EntityNotFoundException::fromEntityAndIdentifier(
                ProductEntity::class,
                $productEntity->getId()
            );
        }

        foreach ($this->items as $item) {
            if ($item->getProduct()->getId() === $productEntity->getId()) {
                return $item;
            }
        }

        throw EntityNotFoundException::fromEntityAndIdentifier(
            ProductEntity::class,
            $productEntity->getId()
        );
    }

    /**
     * @return int
     */
    public function getTotalItemQuantity(): int
    {
        if (null === $this->items) {
            return 0;
        }

        $quantity = 0;
        foreach ($this->items as $item) {
            $quantity += $item->getQuantity();
        }

        return $quantity;
    }

    /**
     * @param ProductAccessorInterface $accessor
     * @param SalesChannelContext $context
     * @return Weight
     */
    public function getTotalWeight(
        ProductAccessorInterface $accessor,
        SalesChannelContext $context
    ): Weight {
        if (null === $this->items) {
            return Weight::aZeroGrams();
        }

        $weight = Weight::aZeroGrams();

        foreach ($this->items as $item) {
            $weight->add(
                $accessor->accessProductWeight(
                    $item->getProduct(),
                    $context
                )->multipliedBy($item->getQuantity())
            );
        }

        return $weight;
    }

    /**
     * @return int
     */
    public function getCountOfDifferentProducts(): int
    {
        if (null === $this->items) {
            return 0;
        }

        $ids = [];

        foreach ($this->items as $item) {
            $ids[] = $item->getProduct()->getId();
        }

        return count(array_unique($ids));
    }

}
