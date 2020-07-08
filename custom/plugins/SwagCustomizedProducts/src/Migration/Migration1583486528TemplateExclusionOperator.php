<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1583486528TemplateExclusionOperator extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1583486528;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
CREATE TABLE `swag_customized_products_template_exclusion_operator` (
    `id`                   BINARY(16)   NOT NULL,
    `operator`             VARCHAR(255) NOT NULL,
    `template_option_type` VARCHAR(255) NOT NULL,
    `created_at`           DATETIME(3)  NOT NULL,
    `updated_at`           DATETIME(3)  NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->exec($query);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
