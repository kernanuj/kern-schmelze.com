<?php declare(strict_types=1);

namespace Mwoelk\CookieAccept\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1589878309DefaultConfig extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1589878309;
    }

    public function update(Connection $connection): void
    {
        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => 'MwoelkCookieAccept.config.active',
            'configuration_value' => '{"_value": true}',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)
        ]);

        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => 'MwoelkCookieAccept.config.reloadPage',
            'configuration_value' => '{"_value": false}',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)
        ]);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
