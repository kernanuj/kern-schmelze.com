<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1568640060TemplateOptionValue extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1568640060;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
CREATE TABLE IF NOT EXISTS `swag_customized_products_template_option_value` (
    `id`                         BINARY(16)   NOT NULL,
    `version_id`                 BINARY(16)   NOT NULL,
    `template_option_id`         BINARY(16)   NOT NULL,
    `template_option_version_id` BINARY(16)   NOT NULL,
    `tax_id`                     BINARY(16)   NULL,
    `value`                      JSON         NULL,
    `item_number`                VARCHAR(255) NULL,
    `default`                    TINYINT(1)   DEFAULT 0 NOT NULL,
    `one_time_surcharge`         TINYINT(1)   DEFAULT 0 NOT NULL,
    `relative_surcharge`         TINYINT(1)   DEFAULT 0 NOT NULL,
    `advanced_surcharge`         TINYINT(1)   DEFAULT 0 NOT NULL,
    `price`                      JSON         NULL,
    `percentage_surcharge`       DOUBLE        NULL,
    `position`                   INT(11)      NOT NULL,
    `created_at`                 DATETIME(3)  NOT NULL,
    `updated_at`                 DATETIME(3)  NULL,
    PRIMARY KEY (`id`, `version_id`),
    CONSTRAINT `json.swag_customized_products_template_option_value.value` CHECK (JSON_VALID(`value`)),
    CONSTRAINT `json.swag_customized_products_template_option_value.price` CHECK (JSON_VALID(`price`)),
    CONSTRAINT `fk.swag_cupro_template_option_value.template_option_id` FOREIGN KEY (`template_option_id`, `template_option_version_id`)
        REFERENCES `swag_customized_products_template_option` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.swag_cupr_template_option_value.tax_id` FOREIGN KEY (`tax_id`)
        REFERENCES `tax` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
