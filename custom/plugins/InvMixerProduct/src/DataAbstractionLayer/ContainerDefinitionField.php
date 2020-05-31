<?php declare(strict_types=1);

namespace InvMixerProduct\DataAbstractionLayer;

use Shopware\Core\Framework\DataAbstractionLayer\Field\ObjectField;

/**
 * Class ContainerDefinitionField
 * @package InvMixerProduct\DataAbstractionLayer
 */
class ContainerDefinitionField extends ObjectField
{
    /**
     * ContainerDefinitionField constructor.
     * @param string $storageName
     * @param string $propertyName
     */
    public function __construct(string $storageName, string $propertyName)
    {
        parent::__construct($storageName, $propertyName);
    }

    /**
     * @return string
     */
    protected function getSerializerClass(): string
    {
        return ContainerDefinitionFieldSerializer::class;
    }

    /**
     * @return string|null
     */
    protected function getAccessorBuilderClass(): ?string
    {
        return null;
    }
}
