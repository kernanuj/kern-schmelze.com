<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\TemplateOption;

use Doctrine\DBAL\Connection;
use Iterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PostWriteValidationEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteContext;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteException;
use Shopware\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Shopware\Core\PlatformRequest;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionValidator;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ColorPicker;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ColorSelect;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Constraint\HexColor;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\HtmlEditor;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageSelect;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\OptionTypeCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\OptionTypeInterface;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Select;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\TextField;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\TemplateOptionValueDefinition;
use Swag\CustomizedProducts\Template\TemplateEntity;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use function json_decode;
use function json_encode;

class TemplateOptionValidatorTest extends TestCase
{
    use ServicesTrait;
    use AdminApiTestBehaviour;

    /**
     * @var WriteContext
     */
    private $context;

    /**
     * @var TemplateOptionValidator
     */
    private $optionTypeValidator;

    /**
     * @var TemplateOptionDefinition
     */
    private $templateOptionDefinition;

    /**
     * @var OptionTypeCollection|MockObject
     */
    private $collection;

    /**
     * @var TemplateOptionValueDefinition
     */
    private $templateOptionValueDefinition;

    public function setUp(): void
    {
        $this->context = WriteContext::createFromContext(Context::createDefaultContext());
        $this->collection = $this->createMock(OptionTypeCollection::class);
        $this->optionTypeValidator = new TemplateOptionValidator(
            $this->getContainer()->get('validator'),
            $this->collection,
            $this->getContainer()->get(Connection::class)
        );
        $this->templateOptionDefinition = $this->getContainer()->get(TemplateOptionDefinition::class);
        $this->templateOptionValueDefinition = $this->getContainer()->get(TemplateOptionValueDefinition::class);
    }

    /**
     * @dataProvider dataProviderTestTemplateOptionHasValidType
     */
    public function testInsertValidWithoutTypeProperties(array $option): void
    {
        $types = $this->getTypeNames();
        $commands = $this->createCommands($option);
        $this->collection->expects(static::once())->method('getNames')->willReturn($types);

        $event = new PostWriteValidationEvent($this->context, $commands);
        $this->optionTypeValidator->postValidate($event);
        $event->getExceptions()->tryToThrow();
    }

    public function testInsertInvalidType(): void
    {
        $option = $this->getTemplateOptionArray(['type' => 'something-definitely-not-existing']);
        $types = $this->getTypeNames();

        $commands = $this->createCommands($option);

        $this->collection->expects(static::once())->method('getNames')->willReturn($types);

        $this->expectException(WriteConstraintViolationException::class);
        try {
            $event = new PostWriteValidationEvent($this->context, $commands);
            $this->optionTypeValidator->postValidate($event);
            $event->getExceptions()->tryToThrow();
            static::fail('Exception was not thrown');
        } catch (WriteException $stackException) {
            static::assertCount(1, $stackException->getExceptions());
            /** @var WriteConstraintViolationException $constraintViolationException */
            $constraintViolationException = $stackException->getExceptions()[0];
            static::assertCount(1, $constraintViolationException->getViolations());
            static::assertSame(
                'This "type" value (something-definitely-not-existing) is invalid.',
                $constraintViolationException->getViolations()->get(0)->getMessage()
            );
            throw $constraintViolationException;
        }
    }

    public function testInsertRequiredCheckboxReturnsConstraintViolation(): void
    {
        $option = $this->getTemplateOptionArray(
            [
                'type' => Checkbox::NAME,
                'required' => true,
            ]
        );
        $types = $this->getTypeNames();

        $commands = $this->createCommands($option);

        $this->collection->expects(static::once())->method('getNames')->willReturn($types);

        $this->expectException(WriteConstraintViolationException::class);
        try {
            $event = new PostWriteValidationEvent($this->context, $commands);
            $this->optionTypeValidator->postValidate($event);
            $event->getExceptions()->tryToThrow();
            static::fail('Exception was not thrown');
        } catch (WriteException $stackException) {
            static::assertCount(1, $stackException->getExceptions());
            /** @var WriteConstraintViolationException $constraintViolationException */
            $constraintViolationException = $stackException->getExceptions()[0];
            static::assertCount(1, $constraintViolationException->getViolations());
            static::assertSame(
                'The property "required" is prohibited for options of the type "checkbox".',
                $constraintViolationException->getViolations()->get(0)->getMessage()
            );
            throw $constraintViolationException;
        }
    }

    public function testInsertWithNoType(): void
    {
        $option = $this->getTemplateOptionArray();
        unset($option['type']);

        $commands = $this->createCommands($option);

        $this->expectException(WriteConstraintViolationException::class);
        try {
            $event = new PostWriteValidationEvent($this->context, $commands);
            $this->optionTypeValidator->postValidate($event);
            $event->getExceptions()->tryToThrow();
            static::fail('Exception was not thrown');
        } catch (WriteException $stackException) {
            static::assertCount(1, $stackException->getExceptions());
            /** @var WriteConstraintViolationException $constraintViolationException */
            $constraintViolationException = $stackException->getExceptions()[0];
            static::assertCount(1, $constraintViolationException->getViolations());
            static::assertSame(
                'This "type" value (NULL) is invalid.',
                $constraintViolationException->getViolations()->get(0)->getMessage()
            );
            throw $constraintViolationException;
        }
    }

    public function testInsertWithEmptyType(): void
    {
        $option = $this->getTemplateOptionArray(['type' => null]);

        $commands = $this->createCommands($option);

        $this->expectException(WriteConstraintViolationException::class);
        try {
            $event = new PostWriteValidationEvent($this->context, $commands);
            $this->optionTypeValidator->postValidate($event);
            $event->getExceptions()->tryToThrow();
            static::fail('Exception was not thrown');
        } catch (WriteException $stackException) {
            static::assertCount(1, $stackException->getExceptions());
            $constraintViolationException = $stackException->getExceptions()[0];
            /** @var WriteConstraintViolationException $constraintViolationException */
            static::assertCount(1, $constraintViolationException->getViolations());
            static::assertSame(
                'This "type" value (NULL) is invalid.',
                $constraintViolationException->getViolations()->get(0)->getMessage()
            );
            throw $constraintViolationException;
        }
    }

    public function testInsertWithEmptyStringAsType(): void
    {
        $option = $this->getTemplateOptionArray(['type' => '']);

        $commands = $this->createCommands($option);

        $this->expectException(WriteConstraintViolationException::class);
        try {
            $event = new PostWriteValidationEvent($this->context, $commands);
            $this->optionTypeValidator->postValidate($event);
            $event->getExceptions()->tryToThrow();
            static::fail('Exception was not thrown');
        } catch (WriteException $stackException) {
            static::assertCount(1, $stackException->getExceptions());
            $constraintViolationException = $stackException->getExceptions()[0];
            /** @var WriteConstraintViolationException $constraintViolationException */
            static::assertCount(1, $constraintViolationException->getViolations());
            static::assertSame(
                'This "type" value () is invalid.',
                $constraintViolationException->getViolations()->get(0)->getMessage()
            );
            throw $constraintViolationException;
        }
    }

    public function testInsertValidWithValidTypeProperties(): void
    {
        $option = $this->getTemplateOptionArray(['type' => Checkbox::NAME]);
        $option['type_properties'] = json_encode([
            'label' => 'foo',
            'description' => 'foo',
        ]);
        $types = $this->getTypeNames();

        $commands = $this->createCommands($option);

        $instance = $this->createMock(Checkbox::class);
        $instance->expects(static::once())->method('getConstraints')->willReturn([
            'label' => [new NotBlank()],
            'description' => [new NotBlank()],
        ]);
        $instance->expects(static::once())->method('getName')->willReturn(Checkbox::NAME);

        $this->collection->expects(static::once())->method('getNames')->willReturn($types);
        $this->collection->expects(static::once())->method('getIterator')->willReturn($this->getGenerator($instance));

        $event = new PostWriteValidationEvent($this->context, $commands);
        $this->optionTypeValidator->postValidate($event);
        $event->getExceptions()->tryToThrow();
    }

    public function testUpdateWithInvalidPrice(): void
    {
        $option = $this->getTemplateOptionArray(['type' => Checkbox::NAME]);
        $option['relative_surcharge'] = 0;
        $option['type_properties'] = json_encode([
            'label' => 'foo',
            'description' => 'foo',
        ]);
        $types = $this->getTypeNames();

        $id = Uuid::randomBytes();
        $commands = [];
        $commands[] = new UpdateCommand(
            $this->templateOptionDefinition,
            $option,
            ['id' => $id],
            $this->createMock(EntityExistence::class),
            ''
        );

        $instance = $this->createMock(Checkbox::class);
        $instance->expects(static::once())->method('getConstraints')->willReturn([
            'label' => [new NotBlank()],
            'description' => [new NotBlank()],
        ]);
        $instance->expects(static::once())->method('getName')->willReturn(Checkbox::NAME);

        $this->collection->expects(static::once())->method('getNames')->willReturn($types);
        $this->collection->expects(static::once())->method('getIterator')->willReturn($this->getGenerator($instance));

        $event = new PostWriteValidationEvent($this->context, $commands);
        $this->optionTypeValidator->postValidate($event);

        try {
            $event->getExceptions()->tryToThrow();
        } catch (WriteException $exception) {
            static::assertStringContainsString('The property "price" should be set', $exception->getMessage());
        }
    }

    public function testUpdateWithInvalidPercentageSurcharge(): void
    {
        $option = $this->getTemplateOptionArray(['type' => Checkbox::NAME]);
        $option['relative_surcharge'] = 1;
        $option['type_properties'] = json_encode([
            'label' => 'foo',
            'description' => 'foo',
        ]);
        $types = $this->getTypeNames();

        $id = Uuid::randomBytes();
        $commands = [];
        $commands[] = new UpdateCommand(
            $this->templateOptionDefinition,
            $option,
            ['id' => $id],
            $this->createMock(EntityExistence::class),
            ''
        );

        $instance = $this->createMock(Checkbox::class);
        $instance->expects(static::once())->method('getConstraints')->willReturn([
            'label' => [new NotBlank()],
            'description' => [new NotBlank()],
        ]);
        $instance->expects(static::once())->method('getName')->willReturn(Checkbox::NAME);

        $this->collection->expects(static::once())->method('getNames')->willReturn($types);
        $this->collection->expects(static::once())->method('getIterator')->willReturn($this->getGenerator($instance));

        $event = new PostWriteValidationEvent($this->context, $commands);
        $this->optionTypeValidator->postValidate($event);

        try {
            $event->getExceptions()->tryToThrow();
        } catch (WriteException $exception) {
            static::assertStringContainsString('The property "percentageSurcharge" should be set', $exception->getMessage());
        }
    }

    public function testUpdateValid(): void
    {
        $option = $this->getTemplateOptionArray(['type' => Checkbox::NAME]);
        $option['type_properties'] = json_encode([
            'label' => 'foo',
            'description' => 'foo',
        ]);
        $types = $this->getTypeNames();

        $id = Uuid::randomBytes();
        $commands = [];
        $commands[] = new UpdateCommand(
            $this->templateOptionDefinition,
            $option,
            ['id' => $id],
            $this->createMock(EntityExistence::class),
            ''
        );

        $instance = $this->createMock(Checkbox::class);
        $instance->expects(static::once())->method('getConstraints')->willReturn([
            'label' => [new NotBlank()],
            'description' => [new NotBlank()],
        ]);
        $instance->expects(static::once())->method('getName')->willReturn(Checkbox::NAME);

        $this->collection->expects(static::once())->method('getNames')->willReturn($types);
        $this->collection->expects(static::once())->method('getIterator')->willReturn($this->getGenerator($instance));

        $event = new PostWriteValidationEvent($this->context, $commands);
        $this->optionTypeValidator->postValidate($event);
        $event->getExceptions()->tryToThrow();
    }

    public function testInsertInvalidColorSelect(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $valueId = Uuid::randomHex();
        $value = '#f';

        $templateData = $this->getColorSelectTemplateData($templateId, $productId, $optionId, $valueId, $value);
        $this->createTemplate($templateData, $templateId);

        static::assertSame(
            Response::HTTP_BAD_REQUEST,
            $this->getBrowser()->getResponse()->getStatusCode(),
            (string) $this->getBrowser()->getResponse()->getContent()
        );

        $response = json_decode((string) $this->getBrowser()->getResponse()->getContent(), true);
        $invalidHexError = $response['errors'][0];
        static::assertSame((new HexColor())->message, $invalidHexError['template']);
    }

    public function testInsertInvalidImageSelect(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $valueId = Uuid::randomHex();
        $value = '';

        $templateData = $this->getImageSelectTemplateData($templateId, $productId, $optionId, $valueId, $value);
        $this->createTemplate($templateData, $templateId);

        static::assertSame(
            Response::HTTP_BAD_REQUEST,
            $this->getBrowser()->getResponse()->getStatusCode(),
            (string) $this->getBrowser()->getResponse()->getContent()
        );

        $response = json_decode((string) $this->getBrowser()->getResponse()->getContent(), true);
        $error = $response['errors'][0];
        static::assertSame((new NotBlank())->message, $error['template']);
        static::assertStringContainsString('value/_value', $error['source']['pointer']);
    }

    public function testInsertImageSelect(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $valueId = Uuid::randomHex();
        $value = 'this-is-a-uuid';

        $templateData = $this->getImageSelectTemplateData($templateId, $productId, $optionId, $valueId, $value);
        $this->createTemplate($templateData, $templateId);

        static::assertSame(
            Response::HTTP_NO_CONTENT,
            $this->getBrowser()->getResponse()->getStatusCode()
        );
    }

    public function testUpdateValidColorSelect(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $valueId = Uuid::randomHex();
        $value = '#ffffff';

        $templateData = $this->getColorSelectTemplateData($templateId, $productId, $optionId, $valueId, $value);

        $this->createTemplate($templateData, $templateId);

        $this->getBrowser()->request(
            'PATCH',
            '/api/v' . PlatformRequest::API_VERSION . '/swag-customized-products-template-option-value/' . $valueId,
            [
                'template_option_id' => $optionId,
                'value' => [
                    '_value' => '#ffffffff',
                ],
            ]
        );
        static::assertSame(
            Response::HTTP_NO_CONTENT,
            $this->getBrowser()->getResponse()->getStatusCode(),
            (string) $this->getBrowser()->getResponse()->getContent()
        );

        $this->getBrowser()->request(
            'PATCH',
            '/api/v' . PlatformRequest::API_VERSION . '/swag-customized-products-template-option-value/' . $valueId,
            [
                'template_option_id' => $optionId,
                'value' => [
                    '_value' => '#ffffff',
                ],
            ]
        );
        static::assertSame(
            Response::HTTP_NO_CONTENT,
            $this->getBrowser()->getResponse()->getStatusCode(),
            (string) $this->getBrowser()->getResponse()->getContent()
        );
    }

    public function testUpdateInvalidColorSelect(): void
    {
        $templateId = Uuid::randomHex();
        $optionId = Uuid::randomHex();
        $productId = Uuid::randomHex();
        $valueId = Uuid::randomHex();
        $value = '#ffffff';

        $templateData = $templateData = $this->getColorSelectTemplateData($templateId, $productId, $optionId, $valueId, $value);

        $this->createTemplate($templateData, $templateId);

        $this->getBrowser()->request(
            'PATCH',
            '/api/v' . PlatformRequest::API_VERSION . '/swag-customized-products-template-option-value/' . $valueId,
            [
                'template_option_id' => $optionId,
                'value' => [
                    '_value' => '#fff',
                ],
            ]
        );
        static::assertSame(
            Response::HTTP_BAD_REQUEST,
            $this->getBrowser()->getResponse()->getStatusCode(),
            (string) $this->getBrowser()->getResponse()->getContent()
        );

        $response = json_decode((string) $this->getBrowser()->getResponse()->getContent(), true);
        $invalidHexError = $response['errors'][0];
        static::assertSame((new HexColor())->message, $invalidHexError['template']);

        $this->getBrowser()->request(
            'PATCH',
            '/api/v' . PlatformRequest::API_VERSION . '/swag-customized-products-template-option-value/' . $valueId,
            [
                'template_option_id' => $optionId,
                'value' => [
                    '_value' => '',
                ],
            ]
        );
        static::assertSame(
            Response::HTTP_BAD_REQUEST,
            $this->getBrowser()->getResponse()->getStatusCode(),
            (string) $this->getBrowser()->getResponse()->getContent()
        );

        $response = json_decode((string) $this->getBrowser()->getResponse()->getContent(), true);
        $invalidHexError = $response['errors'][0];
        static::assertSame((new NotBlank())->message, $invalidHexError['template']);
    }

    public function dataProviderTestTemplateOptionHasValidType(): array
    {
        return [
            [$this->getTemplateOptionArray(['type' => TextField::NAME])],
            [$this->getTemplateOptionArray(['type' => Select::NAME])],
            [$this->getTemplateOptionArray(['type' => ColorPicker::NAME])],
            [$this->getTemplateOptionArray(['type' => ImageSelect::NAME])],
            [$this->getTemplateOptionArray(['type' => Checkbox::NAME])],
            [$this->getTemplateOptionArray(['type' => HtmlEditor::NAME])],
        ];
    }

    private function createTemplate(array $templateData, string $templateId): TemplateEntity
    {
        $this->getBrowser()->request(
            'POST',
            '/api/v' . PlatformRequest::API_VERSION . '/swag-customized-products-template',
            $templateData
        );

        return $this->assertTemplate($templateId);
    }

    private function assertTemplate(string $templateId): TemplateEntity
    {
        /** @var EntityRepositoryInterface $templateRepo */
        $templateRepo = $this->getContainer()->get('swag_customized_products_template.repository');
        $criteria = (new Criteria())
            ->addAssociation('options.prices')
            ->addAssociation('options.values.prices')
            ->addAssociation('products');

        /** @var TemplateEntity|null $template */
        $template = $templateRepo->search($criteria, Context::createDefaultContext())->get($templateId);
        static::assertNotNull($template);

        return $template;
    }

    private function getTypesOfCollection(): array
    {
        return [
            new TextField(),
            new Select(),
            new ColorPicker(),
            new ImageSelect(),
            new Checkbox(),
            new HtmlEditor(),
        ];
    }

    private function getTypeNames(): array
    {
        return [
            TextField::NAME,
            Select::NAME,
            ColorPicker::NAME,
            ImageSelect::NAME,
            Checkbox::NAME,
            HtmlEditor::NAME,
        ];
    }

    private function getGenerator(?OptionTypeInterface $optionType = null): Iterator
    {
        if ($optionType !== null) {
            yield $optionType;

            return;
        }

        yield from $this->getTypesOfCollection();
    }

    /**
     * @return InsertCommand[]
     */
    private function createCommands(array $option): array
    {
        $id = Uuid::randomBytes();
        $commands = [];
        $commands[] = new InsertCommand(
            $this->templateOptionDefinition,
            $option,
            ['id' => $id],
            $this->createMock(EntityExistence::class),
            ''
        );

        return $commands;
    }

    private function getColorSelectTemplateData(
        string $templateId,
        string $productId,
        string $optionId,
        string $valueId,
        string $value
    ): array {
        return [
            'id' => $templateId,
            'internalName' => 'internalName',
            'displayName' => 'displayName',
            'options' => [
                [
                    'id' => $optionId,
                    'displayName' => 'colorSelectOption',
                    'type' => ColorSelect::NAME,
                    'position' => 0,
                    'typeProperties' => [],
                    'values' => [
                        [
                            'id' => $valueId,
                            'displayName' => 'rot',
                            'value' => [
                                '_value' => $value,
                            ],
                            'position' => 1,
                        ],
                    ],
                ],
            ],
            'products' => [
                [
                    'id' => $productId,
                    'name' => 'Own Product',
                    'manufacturer' => [
                        'id' => Uuid::randomHex(),
                        'name' => 'amazing brand',
                    ],
                    'productNumber' => 'P1234',
                    'tax' => ['id' => Uuid::randomHex(), 'taxRate' => 19, 'name' => 'tax'],
                    'price' => [
                        [
                            'currencyId' => Defaults::CURRENCY,
                            'gross' => 10,
                            'net' => 12,
                            'linked' => false,
                        ],
                    ],
                    'stock' => 10,
                ],
            ],
        ];
    }

    private function getImageSelectTemplateData(
        string $templateId,
        string $productId,
        string $optionId,
        string $valueId,
        string $value
    ): array {
        return [
            'id' => $templateId,
            'internalName' => 'internalName',
            'displayName' => 'displayName',
            'options' => [
                [
                    'id' => $optionId,
                    'displayName' => 'imageSelectOption',
                    'type' => ImageSelect::NAME,
                    'position' => 0,
                    'typeProperties' => [],
                    'values' => [
                        [
                            'id' => $valueId,
                            'displayName' => 'best-image-ever',
                            'value' => [
                                '_value' => $value,
                            ],
                            'position' => 1,
                        ],
                    ],
                ],
            ],
            'products' => [
                [
                    'id' => $productId,
                    'name' => 'Own Product',
                    'manufacturer' => [
                        'id' => Uuid::randomHex(),
                        'name' => 'amazing brand',
                    ],
                    'productNumber' => 'P1234',
                    'tax' => ['id' => Uuid::randomHex(), 'taxRate' => 19, 'name' => 'tax'],
                    'price' => [
                        [
                            'currencyId' => Defaults::CURRENCY,
                            'gross' => 10,
                            'net' => 12,
                            'linked' => false,
                        ],
                    ],
                    'stock' => 10,
                ],
            ],
        ];
    }
}
