<?php declare(strict_types=1);


namespace InvMixerProduct\DataObject;

use InvMixerProduct\Value\Identifier;
use InvMixerProduct\Value\Label;
use InvMixerProduct\Struct\ContainerDefinition;
use InvMixerProduct\Value\Weight;
use InvMixerProduct\Value\Price;
use Shopware\Core\Checkout\Customer\CustomerEntity;

class MixView {

    /**
     * @var Identifier
     */
    private $mixId;

    /**
     * @var Label
     */
    private $mixLabel;

    /**
     * @var Price
     */
    private $mixTotalPrice;

    /**
     * @var Weight
     */
    private $mixTotalWeight;

    /**
     * @var ContainerDefinition
     */
    private $containerDefinition;

    /**
     * @var CustomerEntity|null
     */
    private $customer;

    /**
     * MixView constructor.
     * @param Identifier $mixId
     * @param Label $mixLabel
     * @param Price $mixTotalPrice
     * @param Weight $mixTotalWeight
     * @param ContainerDefinition $containerDefinition
     * @param CustomerEntity|null $customer
     */
    public function __construct(
        Identifier $mixId,
        Label $mixLabel,
        Price $mixTotalPrice,
        Weight $mixTotalWeight,
        ContainerDefinition $containerDefinition,
        ?CustomerEntity $customer
    ) {
        $this->mixId = $mixId;
        $this->mixLabel = $mixLabel;
        $this->mixTotalPrice = $mixTotalPrice;
        $this->mixTotalWeight = $mixTotalWeight;
        $this->containerDefinition = $containerDefinition;
        $this->customer = $customer;
    }


    /**
     * @return Identifier
     */
    public function getMixId(): Identifier
    {
        return $this->mixId;
    }

    /**
     * @return Label
     */
    public function getMixLabel(): Label
    {
        return $this->mixLabel;
    }

    /**
     * @return Price
     */
    public function getMixTotalPrice(): Price
    {
        return $this->mixTotalPrice;
    }

    /**
     * @return Weight
     */
    public function getMixTotalWeight(): Weight
    {
        return $this->mixTotalWeight;
    }

    /**
     * @return ContainerDefinition
     */
    public function getContainerDefinition(): ContainerDefinition
    {
        return $this->containerDefinition;
    }

    /**
     * @return CustomerEntity|null
     */
    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }






}
