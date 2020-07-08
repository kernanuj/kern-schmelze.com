<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1564059645Template extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1564059645;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
CREATE TABLE IF NOT EXISTS `swag_customized_products_template` (
    `id`                BINARY(16)                              NOT NULL,
    `version_id`        BINARY(16)                              NOT NULL,
    `parent_version_id` BINARY(16)                              NOT NULL,
    `media_id`          BINARY(16)                              NULL,
    `internal_name`     VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `active`            TINYINT(1)   DEFAULT 0                  NOT NULL,                              
    `step_by_step`      TINYINT(1)   DEFAULT 0                  NOT NULL,                              
    `confirm_input`     TINYINT(1)   DEFAULT 0                  NOT NULL,                              
    `created_at`        DATETIME(3)                             NOT NULL,
    `updated_at`        DATETIME(3)                             NULL,
    PRIMARY KEY (`id`, `version_id`),
    CONSTRAINT `uniq.swag_cupr_template_id__parent_version_id` UNIQUE (`id`, `version_id`, `parent_version_id`),
    CONSTRAINT `fk.swag_cupr_template.media_id` FOREIGN KEY (`media_id`)
        REFERENCES `media` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
