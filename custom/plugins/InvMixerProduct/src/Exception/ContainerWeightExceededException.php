<?php declare(strict_types=1);

namespace InvMixerProduct\Exception;

use Exception;
use InvMixerProduct\Struct\ContainerDefinition;
use InvMixerProduct\Value\Weight;

/**
 * Class ContainerWeightExceededException
 * @package InvMixerProduct\Exception
 */
class ContainerWeightExceededException extends Exception
{

    /**
     * @param ContainerDefinition $containerDefinition
     * @param Weight $weight
     * @return static
     */
    public static function fromContainerAndWeight(
        ContainerDefinition $containerDefinition,
        Weight $weight
    ): self {
        return new self(
            sprintf(
                'The weight %s would exceed the maximum allowed weight of %s.',
                $weight,
                $containerDefinition->getMaxContainerWeight()
            )
        );
    }
}
