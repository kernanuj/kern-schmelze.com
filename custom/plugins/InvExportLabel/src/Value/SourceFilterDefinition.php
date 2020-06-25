<?php declare(strict_types=1);


namespace InvExportLabel\Value;

use Shopware\Core\System\StateMachine\StateMachineEntity;

/**
 * Class SourceFilterDefinition
 * @package InvExportLabel\Value
 */
class SourceFilterDefinition {


    /**
     * @var \DateTime
     */
    private $orderedAtFrom;

    /**
     * @var \DateTime
     */
    private $orderedAtTo;

    /**
     * @var string[]
     */
    private $states;

    /**
     * SourceFilterDefinition constructor.
     * @param \DateTime $orderedAtFrom
     * @param \DateTime $orderedAtTo
     * @param string[] $states
     */
    public function __construct(\DateTime $orderedAtFrom, \DateTime $orderedAtTo, array $states)
    {
        $this->orderedAtFrom = $orderedAtFrom;
        $this->orderedAtTo = $orderedAtTo;
        $this->states = $states;
    }

    /**
     * @return \DateTime
     */
    public function getOrderedAtFrom(): \DateTime
    {
        return $this->orderedAtFrom;
    }

    /**
     * @return \DateTime
     */
    public function getOrderedAtTo(): \DateTime
    {
        return $this->orderedAtTo;
    }

    /**
     * @return string[]
     */
    public function getStates(): array
    {
        return $this->states;
    }
}
