<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use PDO;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\ChangeSet;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PostWriteValidationEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Exception\OptionTypeClassNotFoundException;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ColorSelect;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Constraint\HexColor;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageSelect;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\OptionTypeCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\OptionTypeInterface;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\TemplateOptionValueDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function array_key_exists;
use function array_keys;
use function in_array;
use function is_string;
use function json_decode;
use function sprintf;
use function str_replace;

class TemplateOptionValidator implements EventSubscriberInterface
{
    /**
     * @var OptionTypeCollection
     */
    private $typeCollection;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        ValidatorInterface $validator,
        OptionTypeCollection $typeCollection,
        Connection $connection
    ) {
        $this->validator = $validator;
        $this->typeCollection = $typeCollection;
        $this->connection = $connection;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PreWriteValidationEvent::class => 'preValidate',
            PostWriteValidationEvent::class => 'postValidate',
        ];
    }

    public function preValidate(PreWriteValidationEvent $event): void
    {
        foreach ($event->getCommands() as $command) {
            if (!($command instanceof UpdateCommand)
                || ($command->getDefinition()->getClass() !== TemplateOptionDefinition::class && $command->getDefinition()->getClass() !== TemplateOptionValueDefinition::class)
            ) {
                continue;
            }

            $command->requestChangeSet();
        }
    }

    public function postValidate(PostWriteValidationEvent $event): void
    {
        $violationList = new ConstraintViolationList();
        foreach ($event->getCommands() as $key => $command) {
            // Validate the color select option values
            if (($command instanceof InsertCommand || $command instanceof UpdateCommand)
                && $command->getDefinition()->getClass() === TemplateOptionValueDefinition::class
            ) {
                $type = $this->getOptionTypeOfOptionValue($command);

                if ($type === null || ($type !== ColorSelect::NAME && $type !== ImageSelect::NAME)) {
                    continue;
                }

                if ($type === ColorSelect::NAME) {
                    $this->postValidateColorValue((string) $key, $command, $violationList);
                }

                if ($type === ImageSelect::NAME) {
                    $this->postValidateImageSelectValue((string) $key, $command, $violationList);
                }

                if ($violationList->count() > 0) {
                    $event->getExceptions()->add(new WriteConstraintViolationException($violationList));
                }
                continue;
            }

            if (!($command instanceof InsertCommand || $command instanceof UpdateCommand)
                || $command->getDefinition()->getClass() !== TemplateOptionDefinition::class
            ) {
                continue;
            }

            $changeSet = null;
            if ($command instanceof UpdateCommand) {
                $changeSet = $command->getChangeSet();
            }

            // If a versionized entity is written ignore all constraints
            if (isset($command->getPayload()['version_id'])
                && Uuid::fromBytesToHex($command->getPayload()['version_id']) !== Defaults::LIVE_VERSION
            ) {
                continue;
            }

            // If the first version commit gets inserted, we dont validate
            if (isset($command->getPayload()['type_properties'])) {
                $typeProperties = json_decode($command->getPayload()['type_properties'], true);

                if (isset($typeProperties['optionAdd'])) {
                    continue;
                }
            }

            $payload = $command->getPayload();
            $currentId = Uuid::fromBytesToHex($command->getPrimaryKey()['id']);

            if ($changeSet !== null) {
                $payload = $this->setTypeIfNotSetDuringUpdate($command, $payload, $changeSet);
            }

            if ($command instanceof InsertCommand && !isset($payload['position'])) {
                $violationList->add(
                    $this->buildViolation(
                        'The property "{{ fieldName }}" should be set.',
                        ['{{ fieldName }}' => 'position'],
                        null,
                        sprintf('%s/position', $currentId)
                    )
                );
                continue;
            }

            /** @var string|null $type */
            $type = $payload['type'] ?? null;

            if ($type === null || !$this->validType($type)) {
                $violationList->add(
                    $this->buildViolation(
                        'This "type" value (%value%) is invalid.',
                        ['%value%' => $type ?? 'NULL'],
                        null,
                        sprintf('%s/type', $currentId)
                    )
                );
                continue;
            }

            $extractValue = $this->extractValue($payload);
            $basePath = sprintf('%s/typeProperties', $currentId);

            if ($command instanceof InsertCommand && ! array_key_exists('type_properties', $payload)) {
                $violationList->add(
                    $this->buildViolation(
                        'The property "{{ fieldName }} should be set".',
                        ['{{ fieldName }}' => 'typeProperties'],
                        null,
                        $basePath
                    )
                );
                continue;
            }

            if (!empty($extractValue)) {
                $validations = $this->getOptionType($type)->getConstraints();

                $violationList->addAll($this->validateConsistence($basePath, $validations, $extractValue));
            }

            if ($changeSet !== null) {
                $this->postValidatePriceOnUpdate($currentId, $changeSet, $violationList);
            } else {
                $this->postValidatePriceOnInsert($payload, $currentId, $violationList);
            }

            if ($command instanceof UpdateCommand) {
                $type = $this->getCommandOptionType($command);
            }

            if ($type !== Checkbox::NAME) {
                continue;
            }

            $this->validateCheckboxIsNotRequired($type, $key, $command, $violationList);
        }

        if ($violationList->count() > 0) {
            $event->getExceptions()->add(new WriteConstraintViolationException($violationList));
        }
    }

    private function getOptionType(string $type): OptionTypeInterface
    {
        foreach ($this->typeCollection->getIterator() as $optionType) {
            if ($optionType->getName() !== $type) {
                continue;
            }

            return $optionType;
        }

        throw new OptionTypeClassNotFoundException($type);
    }

    private function validType(string $type): bool
    {
        return in_array($type, $this->typeCollection->getNames(), true);
    }

    private function buildViolation(
        string $messageTemplate,
        array $parameters,
        ?string $root = null,
        ?string $propertyPath = null,
        ?string $invalidValue = null,
        ?string $code = null
    ): ConstraintViolationInterface {
        return new ConstraintViolation(
            str_replace( array_keys($parameters), $parameters, $messageTemplate),
            $messageTemplate,
            $parameters,
            $root,
            $propertyPath,
            $invalidValue,
            null,
            $code
        );
    }

    private function validateConsistence(string $basePath, array $fieldValidations, array $payload): ConstraintViolationList
    {
        $list = new ConstraintViolationList();
        foreach ($fieldValidations as $fieldName => $validations) {
            $currentPath = sprintf('%s/%s', $basePath, $fieldName);
            $list->addAll(
                $this->validator->startContext()
                    ->atPath($currentPath)
                    ->validate($payload[$fieldName] ?? null, $validations)
                    ->getViolations()
            );
        }

        foreach ($payload as $fieldName => $_value) {
            $currentPath = sprintf('%s/%s', $basePath, $fieldName);

            if ( ! array_key_exists($fieldName, $fieldValidations) && $fieldName !== '_name') {
                $list->add(
                    $this->buildViolation(
                        'The property "{{ fieldName }}" is not allowed.',
                        ['{{ fieldName }}' => $fieldName],
                        null,
                        $currentPath
                    )
                );
            }
        }

        return $list;
    }

    private function extractValue(array $payload): array
    {
        if ( ! array_key_exists('type_properties', $payload) || $payload['type_properties'] === null) {
            return [];
        }

        return json_decode($payload['type_properties'], true);
    }

    private function setTypeIfNotSetDuringUpdate(WriteCommand $command, array $payload, ChangeSet $changeSet): array
    {
        if (!($command instanceof UpdateCommand) || $changeSet->hasChanged('type') || $changeSet->getBefore('type') === null) {
            return $payload;
        }

        $payload['type'] = $changeSet->getBefore('type');

        return $payload;
    }

    private function postValidatePriceOnInsert(array $payload, string $currentId, ConstraintViolationList $violationList): void
    {
        if (isset($payload['relative_surcharge']) || isset($payload['price']) || isset($payload['percentage_surcharge'])) {
            $relative_surcharge = (bool) ($payload['relative_surcharge'] ?? false);
            $price = $payload['price'] ?? null;
            $percentage_surcharge = $payload['percentage_surcharge'] ?? null;

            $this->postValidatePrice($currentId, $relative_surcharge, $price !== null, $percentage_surcharge !== null, $violationList);
        }
    }

    private function postValidatePriceOnUpdate(string $currentId, ChangeSet $changeSet, ConstraintViolationList $violationList): void
    {
        if ($changeSet->hasChanged('relative_surcharge') || $changeSet->hasChanged('price') || $changeSet->hasChanged('percentage_surcharge')) {
            $relative_surcharge = (bool) ($changeSet->getAfter('relative_surcharge') ?? $changeSet->hasChanged('relative_surcharge'));
            $price = $changeSet->getAfter('price') ?? $changeSet->getBefore('price');
            $percentage_surcharge = $changeSet->getAfter('percentage_surcharge') ?? $changeSet->getBefore('percentage_surcharge');

            $this->postValidatePrice($currentId, $relative_surcharge, $price !== null, $percentage_surcharge !== null, $violationList);
        }
    }

    private function postValidatePrice(
        string $currentId,
        bool $relativeSurcharge,
        bool $hasPrice,
        bool $hasPercentageSurcharge,
        ConstraintViolationList $violationList
    ): void {
        if ($relativeSurcharge && !$hasPercentageSurcharge) {
            $violationList->add(
                $this->buildViolation(
                    'The property "{{ fieldName }}" should be set.',
                    ['{{ fieldName }}' => 'percentageSurcharge'],
                    null,
                    sprintf('%s/percentageSurcharge', $currentId)
                )
            );

            return;
        }

        if (!$relativeSurcharge && !$hasPrice) {
            $violationList->add(
                $this->buildViolation(
                    'The property "{{ fieldName }}" should be set.',
                    ['{{ fieldName }}' => 'price'],
                    null,
                    sprintf('%s/price', $currentId)
                )
            );

            return;
        }
    }

    private function postValidateColorValue(string $commandKey, WriteCommand $command, ConstraintViolationList $violationList): void
    {
        $value = [];
        if ($command instanceof UpdateCommand) {
            $changeSet = $command->getChangeSet();

            if ($changeSet === null || !$changeSet->hasChanged('value')) {
                return;
            }

            $afterValue = $changeSet->getAfter('value');
            if (! is_string($afterValue)) {
                return;
            }

            $value = json_decode($afterValue, true);
        }

        if ($command instanceof InsertCommand) {
            $payload = $command->getPayload();
            if (isset($payload['value'])) {
                $value = json_decode($payload['value'], true);
            }
        }

        $constraints = [new HexColor(), new NotBlank()];
        $violationList->addAll(
            $this->validator->startContext()
                ->atPath( sprintf('%s/value/_value', $commandKey))
                ->validate($value['_value'] ?? null, $constraints)
                ->getViolations()
        );
    }

    private function postValidateImageSelectValue(string $commandKey, WriteCommand $command, ConstraintViolationList $violationList): void
    {
        $value = [];
        if ($command instanceof UpdateCommand) {
            $changeSet = $command->getChangeSet();

            if ($changeSet === null || !$changeSet->hasChanged('value')) {
                return;
            }

            $afterValue = $changeSet->getAfter('value');
            if (! is_string($afterValue)) {
                return;
            }

            $value = json_decode($afterValue, true);
        }

        if ($command instanceof InsertCommand) {
            $payload = $command->getPayload();
            if (isset($payload['value'])) {
                $value = json_decode($payload['value'], true);
            }
        }

        $constraints = [new NotBlank()];
        $violationList->addAll(
            $this->validator->startContext()
                ->atPath( sprintf('%s/value/_value', $commandKey))
                ->validate($value['_value'] ?? null, $constraints)
                ->getViolations()
        );
    }

    private function getOptionTypeOfOptionValue(WriteCommand $command): ?string
    {
        $optionId = null;
        if ($command instanceof InsertCommand) {
            $payload = $command->getPayload();

            if (!isset($payload['template_option_id'])) {
                return null;
            }

            $optionId = $payload['template_option_id'];
        }

        if ($command instanceof UpdateCommand) {
            $changeSet = $command->getChangeSet();

            if ($changeSet === null) {
                return null;
            }

            $optionId = $changeSet->getBefore('template_option_id');
        }

        if ($optionId === null) {
            return null;
        }

        $query = $this->connection->createQueryBuilder()
            ->select('type')
            ->from('swag_customized_products_template_option')
            ->where('id = :id')
            ->setParameter('id', $optionId)
            ->setMaxResults(1)
            ->execute();

        if (!($query instanceof ResultStatement)) {
            return null;
        }

        return $query->fetch( PDO::FETCH_COLUMN);
    }

    private function getCommandOptionType(UpdateCommand $command): ?string
    {
        $changeSet = $command->getChangeSet();

        if ($changeSet === null) {
            return null;
        }

        $before = $changeSet->getBefore('type');
        if (! is_string($before)) {
            return null;
        }

        return $before;
    }

    private function validateCheckboxIsNotRequired(
        string $type,
        int $key,
        WriteCommand $command,
        ConstraintViolationList $violationList
    ): void {
        $payload = $command->getPayload();
        if ( ! array_key_exists('required', $payload) || !$payload['required']) {
            return;
        }

        $violationList->add(
            $this->buildViolation(
                'The property "{{ property }}" is prohibited for options of the type "{{ type }}".',
                [
                    '{{ property }}' => 'required',
                    '{{ type }}' => $type,
                ],
                null,
                sprintf('%s/required', $key)
            )
        );
    }
}
