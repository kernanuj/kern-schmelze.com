<?php declare(strict_types=1);

namespace InvExportLabel\Migration;

use Doctrine\DBAL\Connection;
use InvExportLabel\Constants;
use InvExportLabel\Service\Core\LabelGenerator;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1597313893 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1597313893;
    }

    public function update(Connection $connection): void
    {
        $config = [
            'pageOrientation' => 'portrait',
            'pageSize' => Constants::LABEL_PDF_PAPER_SIZE,
        ];

        $configJson = \json_encode($config);

        $labelId = Uuid::randomBytes();
        $labelConfigId = Uuid::randomBytes();

        $connection->insert('document_type', ['id' => $labelId, 'technical_name' => LabelGenerator::INV_LABEL, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('document_type_translation', ['document_type_id' => $labelId, 'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM), 'name' => 'InvLabel', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('document_base_config', ['id' => $labelConfigId, 'name' => LabelGenerator::INV_LABEL, 'global' => 1, 'filename_prefix' => 'Etiketten' . '_', 'document_type_id' => $labelId, 'config' => $configJson, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('document_base_config_sales_channel', ['id' => Uuid::randomBytes(), 'document_base_config_id' => $labelConfigId, 'document_type_id' => $labelId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
