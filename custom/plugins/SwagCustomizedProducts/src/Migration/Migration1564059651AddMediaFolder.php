<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use DateTime;
use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1564059651AddMediaFolder extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1564059651;
    }

    public function update(Connection $connection): void
    {
        $defaultFolderId = Uuid::randomBytes();
        $configurationId = Uuid::randomBytes();

        $this->addMediaDefaultFolder($connection, $defaultFolderId);
        $this->addMediaFolderConfiguration($connection, $configurationId);
        $this->addMediaFolder($connection, $defaultFolderId, $configurationId);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function addMediaDefaultFolder(Connection $connection, string $defaultFolderId): void
    {
        $sql = <<<SQL
INSERT IGNORE INTO `media_default_folder` (`id`, `association_fields`, `entity`, `created_at`)
VALUES (:id, '["swagCustomizedProductsTemplate"]', 'swag_customized_products_template', :createdAt);
SQL;

        $connection->executeUpdate($sql, [
            ':id' => $defaultFolderId,
            ':createdAt' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function addMediaFolderConfiguration(Connection $connection, string $configurationId): void
    {
        $sql = <<<SQL
INSERT IGNORE INTO `media_folder_configuration` (`id`, `thumbnail_quality`, `create_thumbnails`, `private`, created_at)
VALUES (:id, 80, 1, 0, :createdAt);
SQL;

        $connection->executeUpdate($sql, [
            ':id' => $configurationId,
            ':createdAt' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function addMediaFolder(Connection $connection, string $defaultFolderId, string $configurationId): void
    {
        $sql = <<<SQL
INSERT IGNORE into `media_folder` (`id`, `name`, `default_folder_id`, `media_folder_configuration_id`, `use_parent_configuration`, `child_count`, `created_at`)
VALUES (:folderId, 'Custom Products Media', :defaultFolderId, :configurationId, 0, 0, :createdAt);
SQL;

        $connection->executeUpdate($sql, [
            ':folderId' => Uuid::randomBytes(),
            ':defaultFolderId' => $defaultFolderId,
            ':configurationId' => $configurationId,
            ':createdAt' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }
}
