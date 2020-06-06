<?php declare(strict_types=1);

namespace Sendcloud\Shipping\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * Class Migration1573059308CreateShipmentsTable
 *
 * @package Sendcloud\Shipping\Migration
 */
class Migration1573059308CreateShipmentsTable extends MigrationStep
{
    public const SHIPMENTS_TABLE = 'sendcloud_shipments';

    /**
     * @inheritDoc
     * @return int
     */
    public function getCreationTimestamp(): int
    {
        return 1573059308;
    }

    /**
     * @inheritDoc
     *
     * @param Connection $connection
     *
     * @throws DBALException
     */
    public function update(Connection $connection): void
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . self::SHIPMENTS_TABLE . '` (
            `id` BINARY(16) NOT NULL,
            `orderNumber` VARCHAR(50) NOT NULL,
            `sendcloudStatus` VARCHAR(50),
            `servicePointId` VARCHAR(50),
            `servicePointInfo` MEDIUMTEXT,
            `trackingNumber` VARCHAR(50),
            `trackingUrl` VARCHAR(500),
            
            PRIMARY KEY (`id`)
        )
        ENGINE = InnoDB
        DEFAULT CHARSET = utf8
        COLLATE = utf8_general_ci;';

        $connection->executeQuery($sql);
    }

    /**
     * @inheritDoc
     *
     * @param Connection $connection
     *
     * @throws DBALException
     */
    public function updateDestructive(Connection $connection): void
    {
        $sql = 'DROP TABLE IF EXISTS `' . self::SHIPMENTS_TABLE . '`';
        $connection->executeQuery($sql);
    }
}
