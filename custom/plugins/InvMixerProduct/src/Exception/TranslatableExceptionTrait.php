<?php declare(strict_types=1);

namespace InvMixerProduct\Exception;

/**
 * Trait TranslateableExceptionTrait
 * @package InvMixerProduct\Exception
 */
trait TranslatableExceptionTrait
{

    /**
     * @var string
     */
    private $messageKey;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @return string
     */
    public function getMessageKey(): string
    {
        return $this->messageKey;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function addParameter(string $key, $value)
    {
        $this->parameters[$key] = $value;

        return $this;
    }

}
