<?php declare(strict_types=1);

namespace InvMixerProduct\Exception;

use InvMixerProduct\Struct\ContainerDefinition;

/**
 * Class NumberOfProductsExceededException
 * @package InvMixerProduct\Exception
 */
class NumberOfProductsExceededException extends \Exception
{

    /**
     * @param ContainerDefinition $containerDefinition
     * @param int $newCount
     * @return static
     */
    public static function fromCountAndContainerDefinition(
        ContainerDefinition $containerDefinition,
        int $newCount
    ): self {
        //@todo: make this tranbslateable whenever there is time
        return new self(
            sprintf(
                'Dein Mix hat bereits die maximale Anzahl (%d) an Zutaten.',
                $containerDefinition->getFillDelimiter()->getAmount()->getValue(),
                $newCount
            )
        );
    }
}
