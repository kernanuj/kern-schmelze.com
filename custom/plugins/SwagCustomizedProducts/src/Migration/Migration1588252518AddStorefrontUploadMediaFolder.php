<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1588252518AddStorefrontUploadMediaFolder extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1588252518;
    }

    public function update(Connection $connection): void
    {
        $parentId = $this->getParentFolderId($connection);
        $configurationId = Uuid::randomBytes();
        $defaultFolderId = Uuid::randomBytes();

        $this->addStorefrontUploadDefaultFolder($connection, $defaultFolderId);
        $this->addCustomerUploadMediaFolderConfiguration($connection, $configurationId);
        $this->addCustomerUploadMediaFolder($connection, $parentId, $configurationId, $defaultFolderId);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function addCustomerUploadMediaFolderConfiguration(Connection $connection, string $configurationId): void
    {
        $query = <<<SQL
INSERT INTO `media_folder_configuration` (`id`, `thumbnail_quality`, `create_thumbnails`, `private`, created_at)
VALUES (:id, 80, 1, 0, :createdAt);
SQL;

        $connection->executeUpdate($query, [
            ':id' => $configurationId,
            ':createdAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function addCustomerUploadMediaFolder(Connection $connection, string $parentId, string $configurationId, string $defaultFolderId): void
    {
        $query = <<<SQL
INSERT INTO `media_folder` (`id`, `name`, `parent_id`, `default_folder_id`, `media_folder_configuration_id`, `use_parent_configuration`, `child_count`, `created_at`)
VALUES (:folderId, 'Storefront uploads', :parentId, :defaultFolderId, :configurationId, 0, 0, :createdAt);
SQL;

        $connection->executeUpdate($query, [
            ':folderId' => Uuid::randomBytes(),
            ':parentId' => $parentId,
            'defaultFolderId' => $defaultFolderId,
            ':configurationId' => $configurationId,
            ':createdAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function getParentFolderId(Connection $connection): string
    {
        $query = <<<SQL
SELECT `id` FROM `media_folder` WHERE `name` = "Custom Products Media";
SQL;

        $id = $connection->fetchColumn($query);
        if (!\is_string($id)) {
            throw new \RuntimeException('Couldn\'t fetch parent id.');
        }

        return $id;
    }

    private function addStorefrontUploadDefaultFolder(Connection $connection, string $defaultFolderId): void
    {
        $sql = <<<SQL
INSERT IGNORE INTO `media_default_folder` (`id`, `association_fields`, `entity`, `created_at`)
VALUES (:id, '["swagCustomizedProductsTemplate"]', 'swag_customized_products_template_storefront_upload', :createdAt);
SQL;

        $connection->executeUpdate($sql, [
            ':id' => $defaultFolderId,
            ':createdAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }
}
