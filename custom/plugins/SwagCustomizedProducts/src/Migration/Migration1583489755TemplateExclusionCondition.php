<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1583489755TemplateExclusionCondition extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1583489755;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
CREATE TABLE `swag_customized_products_template_exclusion_condition` (
    `id`                             BINARY(16)  NOT NULL,
    `version_id`                     BINARY(16)  NOT NULL,
    `template_exclusion_id`          BINARY(16)  NOT NULL,
    `template_exclusion_version_id`  BINARY(16)  NOT NULL,
    `template_exclusion_operator_id` BINARY(16)  NOT NULL,
    `template_option_id`             BINARY(16)  NOT NULL,
    `template_option_version_id`     BINARY(16)  NOT NULL,
    `created_at`                     DATETIME(3) NOT NULL,
    `updated_at`                     DATETIME(3) NULL,
    PRIMARY KEY (`id`,`version_id`),
    CONSTRAINT `fk.swag_cupr_template_exclusion_condition.exclusion_id` FOREIGN KEY (`template_exclusion_id`, `template_exclusion_version_id`)
        REFERENCES `swag_customized_products_template_exclusion` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.swag_cupr_template_exclusion_condition.template_option_id` FOREIGN KEY (`template_option_id`, `template_option_version_id`)
        REFERENCES `swag_customized_products_template_option` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.swag_cupr_template_exclusion_condition.operator_id` FOREIGN KEY (`template_exclusion_operator_id`)
        REFERENCES `swag_customized_products_template_exclusion_operator` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->exec($query);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
