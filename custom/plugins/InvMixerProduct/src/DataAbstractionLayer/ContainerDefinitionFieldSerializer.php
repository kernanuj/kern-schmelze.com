<?php declare(strict_types=1);

namespace InvMixerProduct\DataAbstractionLayer;

use Generator;
use InvMixerProduct\Struct\ContainerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InvalidSerializerFieldException;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\AbstractFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\JsonFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ContainerDefinitionFieldSerializer
 * @package InvMixerProduct\DataAbstractionLayer
 */
class ContainerDefinitionFieldSerializer extends AbstractFieldSerializer
{
    /**
     * ContainerDefinitionFieldSerializer constructor.
     * @param DefinitionInstanceRegistry $definitionRegistry
     * @param ValidatorInterface $validator
     */
    public function __construct(
        DefinitionInstanceRegistry $definitionRegistry,
        ValidatorInterface $validator
    ) {
        parent::__construct($validator, $definitionRegistry);
    }

    /**
     * @param Field $field
     * @param EntityExistence $existence
     * @param KeyValuePair $data
     * @param WriteParameterBag $parameters
     * @return Generator
     */
    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): Generator {
        if (!$field instanceof ContainerDefinitionField) {
            throw new InvalidSerializerFieldException(ContainerDefinitionField::class, $field);
        }

        if ($field->is(Required::class)) {
            $this->validate([new NotBlank()], $data, $parameters->getPath());
        }

        $value = $data->getValue();

        if ($value !== null) {
            $value = JsonFieldSerializer::encodeJson($value);
        }

        yield $field->getStorageName() => $value;
    }

    /**
     * @param Field $field
     * @param $value
     * @return ContainerDefinition|null
     */
    public function decode(Field $field, $value)
    {
        if ($value === null) {
            return null;
        }
        $value = \json_decode($value, true);

        return ContainerDefinition::fromArray($value);
    }
}
