<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1564059646TemplateTranslation extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1564059646;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
CREATE TABLE IF NOT EXISTS `swag_customized_products_template_translation` (
    `swag_customized_products_template_id`         BINARY(16)                              NOT NULL,
    `swag_customized_products_template_version_id` BINARY(16)                              NOT NULL,
    `language_id`                                  BINARY(16)                              NOT NULL,
    `display_name`                                 VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description`                                  LONGTEXT     COLLATE utf8mb4_unicode_ci NULL,
    `created_at`                                   DATETIME(3)                             NOT NULL,
    `updated_at`                                   DATETIME(3)                             NULL,
    PRIMARY KEY (`swag_customized_products_template_id`, `language_id`, `swag_customized_products_template_version_id`),
    CONSTRAINT `fk.swag_cupr_template_translation.template_id` FOREIGN KEY (`swag_customized_products_template_id`, `swag_customized_products_template_version_id`)
        REFERENCES `swag_customized_products_template` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.swag_cupr_template_translation.language_id` FOREIGN KEY (`language_id`)
        REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
