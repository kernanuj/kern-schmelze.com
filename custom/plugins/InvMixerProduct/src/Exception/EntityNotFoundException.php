<?php declare(strict_types=1);

namespace InvMixerProduct\Exception;

/**
 * Class EntityNotFoundException
 * @package InvMixerProduct\Exception
 */
class EntityNotFoundException extends \Exception
{

    /**
     * @param string $entityClassName
     * @param string $identifier
     * @return static
     */
    public static function fromEntityAndIdentifier(
        string $entityClassName,
        string $identifier
    ): self {
        return new self(
            sprintf(
                'An entity of type %s with identifier %s could not be found',
                $entityClassName,
                $identifier)
        );
    }
}
