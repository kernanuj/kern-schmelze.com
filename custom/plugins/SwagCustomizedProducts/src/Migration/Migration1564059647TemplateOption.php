<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1564059647TemplateOption extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1564059647;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
CREATE TABLE IF NOT EXISTS `swag_customized_products_template_option` (
    `id`                   BINARY(16)             NOT NULL,
    `version_id`           BINARY(16)             NOT NULL,
    `template_id`          BINARY(16)             NOT NULL,
    `template_version_id`  BINARY(16)             NOT NULL,
    `type`                 VARCHAR(255)           NOT NULL,
    `type_properties`      JSON                   NULL,
    `item_number`          VARCHAR(255)           NULL,
    `required`             TINYINT(1)   DEFAULT 0 NOT NULL,
    `one_time_surcharge`   TINYINT(1)   DEFAULT 0 NOT NULL,
    `relative_surcharge`   TINYINT(1)   DEFAULT 0 NOT NULL,
    `advanced_surcharge`   TINYINT(1)   DEFAULT 0 NOT NULL,
    `position`             INT(11)                NOT NULL,
    `tax_id`               BINARY(16)             NULL,
    `price`                JSON                   NULL,
    `percentage_surcharge` DOUBLE                 NULL,
    `created_at`           DATETIME(3)            NOT NULL,
    `updated_at`           DATETIME(3)            NULL,
    PRIMARY KEY (`id`, `version_id`),
    CONSTRAINT `json.swag_cupr_template_option.price` CHECK (JSON_VALID(`price`)),
    CONSTRAINT `json.swag_cupr_template_option.type_properties` CHECK (JSON_VALID(`type_properties`)),
    CONSTRAINT `fk.swag_cupr_template_option.template_id` FOREIGN KEY (`template_id`, `template_version_id`)
        REFERENCES `swag_customized_products_template` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.swag_cupr_template_option.tax_id` FOREIGN KEY (`tax_id`)
        REFERENCES `tax` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
