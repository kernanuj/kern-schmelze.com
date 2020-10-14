<?php declare(strict_types=1);


namespace InvMixerProduct\DataObject;

use InvMixerProduct\Struct\ContainerDefinition;
use InvMixerProduct\Value\Identifier;
use InvMixerProduct\Value\Label;
use InvMixerProduct\Value\Price;
use InvMixerProduct\Value\Weight;
use Shopware\Core\Checkout\Customer\CustomerEntity;

class MixView
{

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
     * @var MixViewItemCollection
     */
    private $itemCollection;

    /**
     * @var bool
     */
    private $isFilled = false;

    /**
     * @var bool
     */
    private $isComplete = false;

    /**
     * @var array
     */
    private $mixState = [];

    /**
     * MixView constructor.
     * @param Identifier $mixId
     * @param Label $mixLabel
     * @param Price $mixTotalPrice
     * @param Weight $mixTotalWeight
     * @param ContainerDefinition $containerDefinition
     * @param CustomerEntity|null $customer
     * @param MixViewItemCollection $itemCollection
     */
    public function __construct(
        Identifier $mixId,
        Label $mixLabel,
        Price $mixTotalPrice,
        Weight $mixTotalWeight,
        ContainerDefinition $containerDefinition,
        ?CustomerEntity $customer,
        MixViewItemCollection $itemCollection
    ) {
        $this->mixId = $mixId;
        $this->mixLabel = $mixLabel;
        $this->mixTotalPrice = $mixTotalPrice;
        $this->mixTotalWeight = $mixTotalWeight;
        $this->containerDefinition = $containerDefinition;
        $this->customer = $customer;
        $this->itemCollection = $itemCollection;
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

    /**
     * @return MixViewItemCollection
     */
    public function getItemCollection(): MixViewItemCollection
    {
        return $this->itemCollection;
    }

    /**
     * @return bool
     */
    public function isFilled(): bool
    {
        return $this->isFilled;
    }

    /**
     * @param bool $isFilled
     * @return MixView
     */
    public function setIsFilled(bool $isFilled): MixView
    {
        $this->isFilled = $isFilled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isComplete(): bool
    {
        return $this->isComplete;
    }

    /**
     * @param bool $isComplete
     * @return MixView
     */
    public function setIsComplete(bool $isComplete): MixView
    {
        $this->isComplete = $isComplete;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalItemQuantity(): int
    {
        $quantity = 0;
        foreach ($this->itemCollection->getItems() as $item) {
            $quantity += $item->getQuantity();
        }

        return $quantity;
    }

    /**
     * @return int
     */
    public function getFillDelimiter(): int
    {
        $fillDelimiter = $this->containerDefinition->getFillDelimiter()->getAmount()->getValue();

        return $fillDelimiter;
    }

    /**
     * @return array
     */
    public function getMixState(): array
    {
        return $this->mixState;
    }

    /**
     * @param array $mixState
     * @return MixView
     */
    public function setMixState(array $mixState): MixView
    {
        $this->mixState = $mixState;
        return $this;
    }

}
