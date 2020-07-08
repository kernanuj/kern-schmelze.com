<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1583477457TemplateExclusion extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1583477457;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
CREATE TABLE `swag_customized_products_template_exclusion` (
    `id`                  BINARY(16)   NOT NULL,
    `version_id`          BINARY(16)   NOT NULL,
    `template_id`         BINARY(16)   NOT NULL,
    `template_version_id` BINARY(16)   NOT NULL,
    `name`                VARCHAR(255) NOT NULL,
    `created_at`          DATETIME(3)  NOT NULL,
    `updated_at`          DATETIME(3)  NULL,
    PRIMARY KEY (`id`,`version_id`),
    CONSTRAINT `fk.swag_customized_products_template_exclusion.template_id` FOREIGN KEY (`template_id`,`template_version_id`)
        REFERENCES `swag_customized_products_template` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->exec($query);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
