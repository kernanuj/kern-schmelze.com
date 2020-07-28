<?php declare(strict_types=1);

namespace InvMixerProduct\Exception;

use Exception;
use InvMixerProduct\Entity\MixEntity;

/**
 * Class InsufficientMixComponentsException
 * @package InvMixerProduct\Exception
 */
class InsufficientMixComponentsException extends Exception
{

    /**
     * @param MixEntity $mix
     * @return static
     */
    public static function fromMixHasNoChildren(
        MixEntity $mix
    ): self {
        return new self(
            sprintf(
                'The mix cannot be converted to a cart item when no children are added.',
            )
        );
    }
}
