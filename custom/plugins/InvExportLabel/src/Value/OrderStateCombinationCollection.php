<?php declare(strict_types=1);


namespace InvExportLabel\Value;

/**
 * Class OrderStateCombinationCollection
 * @package InvExportLabel\Value
 */
final class OrderStateCombinationCollection
{

    /**
     * @var OrderStateCombination[]
     */
    private $combinations = [];

    /**
     * @param OrderStateCombination $orderStateCombination
     * @return $this
     */
    public function addCombination(OrderStateCombination $orderStateCombination): self
    {
        $this->combinations[] = $orderStateCombination;
        return $this;
    }

    /**
     * @param OrderStateCombination $search
     * @return bool
     */
    public function hasOneMatching(OrderStateCombination $search): bool
    {

        foreach ($this->getCombinations() as $combination) {
            if (
                $combination->getOrderState() === $search->getOrderState()
                && $combination->getTransactionState() === $search->getTransactionState()
                && $combination->getDeliveryState() === $search->getDeliveryState()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return OrderStateCombination[]
     */
    public function getCombinations(): array
    {
        return $this->combinations;
    }

}
