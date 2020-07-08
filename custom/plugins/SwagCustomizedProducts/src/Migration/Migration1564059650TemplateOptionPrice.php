<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1564059650TemplateOptionPrice extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1564059650;
    }

    public function update(Connection $connection): void
    {
        $connection->executeUpdate('
CREATE TABLE IF NOT EXISTS `swag_customized_products_template_option_price` (
    `id`                         BINARY(16)  NOT NULL,
    `version_id`                 BINARY(16)  NOT NULL,
    `template_option_id`         BINARY(16)  NOT NULL,
    `template_option_version_id` BINARY(16)  NOT NULL,
    `rule_id`                    BINARY(16)  NULL,
    `price`                      JSON        NULL,
    `percentage_surcharge`       DOUBLE      NULL,
    `created_at`                 DATETIME(3) NOT NULL,
    `updated_at`                 DATETIME(3) NULL,
    PRIMARY KEY (`id`, `version_id`),
    CONSTRAINT `uniq.swag_cupr_template_option_price` UNIQUE (`rule_id`, `template_option_id`, `template_option_version_id`),
    CONSTRAINT `json.swag_customized_products_template_option_price.price` CHECK (JSON_VALID(`price`)),
    CONSTRAINT `fk.swag_cupr_template_option_price.template_option_id` FOREIGN KEY (`template_option_id`, `template_option_version_id`)
        REFERENCES `swag_customized_products_template_option` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
