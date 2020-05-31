<?php declare(strict_types=1);

namespace InvMixerProduct\Entity;

use InvMixerProduct\Struct\ContainerDefinition;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

/**
 * Class MixEntity
 * @package InvMixerProduct\Entity
 */
class MixEntity extends Entity {

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

}
