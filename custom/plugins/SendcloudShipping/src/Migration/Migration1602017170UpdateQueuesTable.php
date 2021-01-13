<?php declare(strict_types=1);

namespace Sendcloud\Shipping\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1602017170UpdateQueuesTable extends MigrationStep
{
    public const QUEUES_TABLE = 'sendcloud_queues';

    /**
     * @inheritDoc
     *
     * @return int
     */
    public function getCreationTimestamp(): int
    {
        return 1602017170;
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
        $sql = 'ALTER TABLE `' . self::QUEUES_TABLE . '` 
            CHANGE COLUMN `serializedTask` `serializedTask` MEDIUMBLOB NOT NULL;';

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
        // No need for update destructive
    }
}
