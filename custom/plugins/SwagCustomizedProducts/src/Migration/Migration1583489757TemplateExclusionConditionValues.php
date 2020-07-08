<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1583489757TemplateExclusionConditionValues extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1583489757;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
CREATE TABLE IF NOT EXISTS `swag_customized_products_template_exclusion_condition_values` (
    `template_exclusion_condition_id`                                  BINARY(16)  NOT NULL,
    `swag_customized_products_template_exclusion_condition_version_id` BINARY(16)  NOT NULL,
    `template_option_value_id`                                         BINARY(16)  NOT NULL,
    `swag_customized_products_template_option_value_version_id`        BINARY(16)  NOT NULL,
    `created_at`                                                       DATETIME(3) NOT NULL,
    `updated_at`                                                       DATETIME(3) NULL,
    PRIMARY KEY (`template_exclusion_condition_id`, `template_option_value_id`),
    CONSTRAINT `fk.swag_cupr_template_exclusion_condition_values.condition_id` FOREIGN KEY (`template_exclusion_condition_id`, `swag_customized_products_template_exclusion_condition_version_id`)
        REFERENCES `swag_customized_products_template_exclusion_condition` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.swag_cupr_template_exclusion_condition_values.value_id` FOREIGN KEY (`template_option_value_id`, `swag_customized_products_template_option_value_version_id`)
        REFERENCES `swag_customized_products_template_option_value` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->exec($query);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
