<?php declare(strict_types=1);

namespace InvMixerProduct\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * Class Migration1591004634
 * @package InvMixerProduct\Migration
 */
class Migration1591004634 extends MigrationStep
{
    /**
     * @return int
     */
    public function getCreationTimestamp(): int
    {
        return 1591004634;
    }

    /**
     * @param Connection $connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public function update(Connection $connection): void
    {

        $connection->exec(
            'DROP TABLE IF EXISTS `inv_mixer_product__mix_item`;'
        );

        $sql = <<<SQL

CREATE TABLE `inv_mixer_product__mix_item` (
                                               `id` binary(16) NOT null,
                                               `mix_id` BINARY(16) NOT NULL,
                                               `product_id` BINARY(16) NULL,
                                               `quantity` INT(11) NOT NULL,
                                               `product_version_id` BINARY(16) NOT NULL,
                                               `created_at` DATETIME(3) NOT NULL,
                                               `updated_at` DATETIME(3) NULL,
                                               PRIMARY KEY (`id`),
                                               KEY `fk.inv_mixer_product__mix_item.mix_id` (`mix_id`),
                                               KEY `fk.inv_mixer_product__mix_item.product_id` (`product_id`,`product_version_id`),
                                               CONSTRAINT `fk.inv_mixer_product__mix_item.mix_id` FOREIGN KEY (`mix_id`) REFERENCES `inv_mixer_product__mix` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                                               CONSTRAINT `fk.inv_mixer_product__mix_item.product_id` FOREIGN KEY (`product_id`,`product_version_id`) REFERENCES `product` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->exec($sql);

    }

    /**
     * @param Connection $connection
     */
    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
