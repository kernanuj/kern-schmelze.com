<?php declare(strict_types=1);

namespace InvMixerProduct\Exception;

use InvMixerProduct\Struct\ContainerDefinition;

/**
 * Class NumberOfProductsExceededException
 * @package InvMixerProduct\Exception
 */
class NumberOfProductsExceededException extends \Exception
{

    use TranslatableExceptionTrait;

    /**
     * NumberOfProductsExceededException constructor.
     * @param string $messageKey
     * @param string $message
     * @param int|null $code
     * @param \Throwable|null $previous
     */
    private function __construct(string $messageKey, $message = "", ?int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->messageKey = $messageKey;
    }

    /**
     * @param ContainerDefinition $containerDefinition
     * @param int $newCount
     * @return static
     */
    public static function fromContainerDefinitionIsInsufficientForContent(
        ContainerDefinition $containerDefinition,
        int $newCount
    ): self {
        $self = new self(
            'InvMixerProduct.mix.error.containerDefinitionIsInsufficientForContent',
            sprintf(
                'Deine Schokoladentafel hat bereits die maximale Anzahl (%d) an Zutaten. Du kannst jetzt bestellen.',
                $containerDefinition->getFillDelimiter()->getAmount()->getValue(),
                $newCount
            )
        );

        $self->addParameter('newCount', $newCount);

        return $self;
    }    /**
     * @param ContainerDefinition $containerDefinition
     * @param int $newCount
     * @return static
     */
    public static function fromNoRoomLeftInContainer(
        ContainerDefinition $containerDefinition,
        int $newCount
    ): self {
        $self = new self(
            'InvMixerProduct.mix.error.noRoomLeftInContainer',
            sprintf(
                'Deine Schokoladentafel hat bereits die maximale Anzahl (%d) an Zutaten.',
                $containerDefinition->getFillDelimiter()->getAmount()->getValue(),
                $newCount
            )
        );

        $self->addParameter('newCount', $newCount);

        return $self;
    }
}
