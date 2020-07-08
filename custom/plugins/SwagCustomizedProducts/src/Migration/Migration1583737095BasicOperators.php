<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ColorSelect;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\DateTime;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\HtmlEditor;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageSelect;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\NumberField;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Select;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Textarea;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\TextField;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Timestamp;
use function array_merge;

class Migration1583737095BasicOperators extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1583737095;
    }

    public function update(Connection $connection): void
    {
        $languageIds = $this->getLanguageIdLocaleMapping($connection);

        foreach ($this->getOperatorSet() as $operator) {
            $operatorId = Uuid::randomBytes();
            $translations = $operator['translations'];
            unset($operator['translations']);

            $connection->insert(
                'swag_customized_products_template_exclusion_operator',
                array_merge(
                    [
                        'id' => $operatorId,
                        'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    ],
                    $operator
                )
            );

            $translationsWritten = false;
            foreach ($translations as $locale => $label) {
                $currentLanguageId = $languageIds[$locale];
                if ($currentLanguageId === null) {
                    continue;
                }

                $connection->insert(
                    'swag_customized_products_template_exclusion_operator_translation',
                    [
                        'swag_customized_products_template_exclusion_operator_id' => $operatorId,
                        'language_id' => $currentLanguageId,
                        'label' => $label,
                        'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    ]
                );

                $translationsWritten = true;
            }

            if ($translationsWritten === true) {
                continue;
            }

            // If no translations where written, write the english translations to the default language as fallback
            foreach ($translations as $locale => $label) {
                if ($locale !== 'en-GB') {
                    continue;
                }

                $connection->insert(
                    'swag_customized_products_template_exclusion_operator_translation',
                    [
                        'swag_customized_products_template_exclusion_operator_id' => $operatorId,
                        'language_id' => Defaults::LANGUAGE_SYSTEM,
                        'label' => $label,
                        'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    ]
                );
            }
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function getOperatorSet(): array
    {
        return [
            $this->buildOperator('!X', Checkbox::NAME, 'Nicht ausgewählt', 'Not checked'),
            $this->buildOperator('X', Checkbox::NAME, 'Ausgewählt', 'Checked'),
            $this->buildOperator('!X', ColorSelect::NAME, 'Nichts ausgewählt', 'Nothing selected'),
            $this->buildOperator('X', ColorSelect::NAME, 'Hat Auswahl', 'Has selection'),
            $this->buildOperator('!X', DateTime::NAME, 'Nicht ausgefüllt', 'Not filled'),
            $this->buildOperator('X', DateTime::NAME, 'Ausgefüllt', 'Filled'),
            $this->buildOperator('!X', HtmlEditor::NAME, 'Nicht ausgefüllt', 'Not filled'),
            $this->buildOperator('X', HtmlEditor::NAME, 'Ausgefüllt', 'Filled'),
            $this->buildOperator('!X', ImageSelect::NAME, 'Nichts ausgewählt', 'Nothing selected'),
            $this->buildOperator('X', ImageSelect::NAME, 'Hat Auswahl', 'Has selection'),
            $this->buildOperator('!X', NumberField::NAME, 'Standardwert', 'Standard value'),
            $this->buildOperator('X', NumberField::NAME, 'Nicht Standardwert', 'Not standard value'),
            $this->buildOperator('!X', Select::NAME, 'Nichts ausgewählt', 'Nothing selected'),
            $this->buildOperator('X', Select::NAME, 'Hat Auswahl', 'Has selection'),
            $this->buildOperator('!X', Textarea::NAME, 'Nicht ausgefüllt', 'Not filled'),
            $this->buildOperator('X', Textarea::NAME, 'Ausgefüllt', 'Filled'),
            $this->buildOperator('!X', TextField::NAME, 'Nicht ausgefüllt', 'Not filled'),
            $this->buildOperator('X', TextField::NAME, 'Ausgefüllt', 'Filled'),
            $this->buildOperator('!X', Timestamp::NAME, 'Nicht ausgefüllt', 'Not filled'),
            $this->buildOperator('X', Timestamp::NAME, 'Ausgefüllt', 'Filled'),
        ];
    }

    private function buildOperator(string $operator, string $optionType, string $deDeLabel, string $enGbLabel): array
    {
        return [
            'operator' => $operator,
            'template_option_type' => $optionType,
            'translations' => [
                'de-DE' => $deDeLabel,
                'en-GB' => $enGbLabel,
            ],
        ];
    }

    private function getLanguageIdLocaleMapping(Connection $connection): array
    {
        $query = <<<SQL
SELECT `locale`.`code`, `language`.`id`
FROM `language`
INNER JOIN `locale` on `language`.`locale_id` = `locale`.`id`
WHERE `locale`.`code` IN ('en-GB', 'de-DE');
SQL;

        return $connection->executeQuery(
            $query
        )->fetchAll( PDO::FETCH_KEY_PAIR);
    }
}
