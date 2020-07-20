<?php declare(strict_types=1);


namespace InvExportLabel\Value;

use DateTime;

/**
 * Class SourceFilterDefinition
 * @package InvExportLabel\Value
 */
class SourceFilterDefinition
{


    /**
     * @var DateTime
     */
    private $orderedAtFrom;

    /**
     * @var DateTime
     */
    private $orderedAtTo;

    /**
     * @var OrderStateCombinationCollection
     */
    private $orderStateCombinationCollection;

    /**
     * SourceFilterDefinition constructor.
     * @param DateTime $orderedAtFrom
     * @param DateTime $orderedAtTo
     * @param OrderStateCombinationCollection $orderStateCombinationCollection
     */
    public function __construct(
        DateTime $orderedAtFrom,
        DateTime $orderedAtTo,
        OrderStateCombinationCollection $orderStateCombinationCollection
    ) {
        $this->orderedAtFrom = $orderedAtFrom;
        $this->orderedAtTo = $orderedAtTo;
        $this->orderStateCombinationCollection = $orderStateCombinationCollection;
    }


    /**
     * @return DateTime
     */
    public function getOrderedAtFrom(): DateTime
    {
        return $this->orderedAtFrom;
    }

    /**
     * @param DateTime $orderedAtFrom
     * @return SourceFilterDefinition
     */
    public function setOrderedAtFrom(DateTime $orderedAtFrom): SourceFilterDefinition
    {
        $this->orderedAtFrom = $orderedAtFrom;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getOrderedAtTo(): DateTime
    {
        return $this->orderedAtTo;
    }

    /**
     * @param DateTime $orderedAtTo
     * @return SourceFilterDefinition
     */
    public function setOrderedAtTo(DateTime $orderedAtTo): SourceFilterDefinition
    {
        $this->orderedAtTo = $orderedAtTo;
        return $this;
    }

    /**
     * @return OrderStateCombinationCollection
     */
    public function getOrderStateCombinationCollection(): OrderStateCombinationCollection
    {
        return $this->orderStateCombinationCollection;
    }

    /**
     * @param OrderStateCombinationCollection $orderStateCombinationCollection
     * @return SourceFilterDefinition
     */
    public function setOrderStateCombinationCollection(OrderStateCombinationCollection $orderStateCombinationCollection
    ): SourceFilterDefinition {
        $this->orderStateCombinationCollection = $orderStateCombinationCollection;
        return $this;
    }


}
