<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Helper;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Currency\CurrencyEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Api\TemplateOptionController;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionService;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ColorPicker;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ColorSelect;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\DateTime;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\FileUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\HtmlEditor;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageSelect;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\NumberField;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\OptionTypeCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Select;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Textarea;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\TextField;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Timestamp;
use Swag\CustomizedProducts\Template\TemplateDefinition;

trait ServicesTrait
{
    use IntegrationTestBehaviour;

    /**
     * @var string
     */
    protected $templateName = 'InternalTraitTemplateName';

    /**
     * @var string
     */
    protected $displayName = 'TraitDisplayName';

    protected function getTemplateOptionService(): TemplateOptionService
    {
        return new TemplateOptionService($this->getCollection());
    }

    protected function getTemplateOptionController(): TemplateOptionController
    {
        return new TemplateOptionController($this->getTemplateOptionService());
    }

    protected function getTemplateRepository(): EntityRepositoryInterface
    {
        return $this->getContainer()->get('swag_customized_products_template.repository');
    }

    protected function getExpectedTypes(): array
    {
        $names = [];
        foreach ($this->getCollection() as $optionType) {
            $names[] = $optionType->getName();
        }

        return $names;
    }

    protected function getTemplateOptionArray(array $properties = []): array
    {
        return \array_merge([
            'id' => Uuid::randomBytes(),
            'templateId' => Uuid::randomBytes(),
            'displayName' => 'displayName',
            'type' => TextField::NAME,
            'itemNumber' => '12',
            'position' => 0,
            'type_properties' => '[]',
        ], $properties);
    }

    protected function createTemplate(
        string $templateId,
        Context $context,
        array $additionalData = []
    ): void {
        /** @var EntityRepositoryInterface $templateRepository */
        $templateRepository = $this->getContainer()->get(\sprintf('%s.repository', TemplateDefinition::ENTITY_NAME));

        $templateData = [
            'id' => $templateId,
            'internalName' => $this->templateName,
            'displayName' => $this->displayName,
        ];
        $templateData = \array_merge($templateData, $additionalData);

        $templateRepository->create([
            $templateData,
        ], $context);
    }

    protected function getTemplateOptionsArrayForCreation(array $properties = []): array
    {
        return \array_merge([
            'id' => Uuid::randomHex(),
            'displayName' => 'displayName',
            'type' => Select::NAME,
            'itemNumber' => '12',
            'position' => 0,
            'typeProperties' => [],
            'price' => [
                [
                    'currencyId' => Defaults::CURRENCY,
                    'net' => 10,
                    'gross' => 11.19,
                    'linked' => true,
                ],
            ],
        ], $properties);
    }

    protected function getTemplateOptionValuesArrayForCreation(array $properties = []): array
    {
        return \array_merge([
            'id' => Uuid::randomHex(),
            'displayName' => 'some options',
            'position' => 0,
            'value' => [
                'test' => 123,
                'foo' => 'bar',
            ],
            'price' => [
                [
                    'currencyId' => Defaults::CURRENCY,
                    'price' => [
                        [
                            'currencyId' => Defaults::CURRENCY,
                            'net' => 10,
                            'gross' => 11.9,
                            'linked' => true,
                        ],
                    ],
                ],
            ],
        ], $properties);
    }

    protected function getTestCurrency(Context $context, array $additionalOptions = []): CurrencyEntity
    {
        $id = Uuid::randomHex();
        $currencyRepo = $this->getContainer()->get('currency.repository');

        $currencyRepo->create([
            \array_merge([
                'id' => $id,
                'symbol' => '¥',
                'decimalPrecision' => 2,
                'factor' => 100,
                'shortName' => 'Yen',
                'isoCode' => 'JPY',
                'name' => 'japanese Yen',
            ], $additionalOptions),
        ], $context);

        return $currencyRepo->search(new Criteria([$id]), $context)->first();
    }

    protected function getOperatorIdForType(EntityRepositoryInterface $operatorRepository, string $optionType = 'checkbox'): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('templateOptionType', $optionType)
        );
        $criteria->addSorting(
            new FieldSorting('id', \random_int(0, 1) === 1 ? FieldSorting::ASCENDING : FieldSorting::DESCENDING)
        );

        $operatorId = $operatorRepository->searchIds($criteria, Context::createDefaultContext())->firstId();
        static::assertNotNull($operatorId);

        return $operatorId;
    }

    private function getCollection(): OptionTypeCollection
    {
        $collection = new OptionTypeCollection([]);
        $collection->add(new Checkbox());
        $collection->add(new ColorPicker());
        $collection->add(new ColorSelect());
        $collection->add(new DateTime());
        $collection->add(new FileUpload());
        $collection->add(new HtmlEditor());
        $collection->add(new ImageSelect());
        $collection->add(new ImageUpload());
        $collection->add(new NumberField());
        $collection->add(new Select());
        $collection->add(new Textarea());
        $collection->add(new TextField());
        $collection->add(new Timestamp());

        return $collection;
    }
}
