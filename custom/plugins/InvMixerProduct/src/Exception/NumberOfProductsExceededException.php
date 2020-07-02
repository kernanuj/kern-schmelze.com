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
        return new self(
            sprintf(
                'The maximum number of products %d is exceeded with %d',
                $containerDefinition->getFillDelimiter()->getAmount()->getValue(),
                $newCount
            )
        );
    }
}
