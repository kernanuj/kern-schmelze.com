<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Util\Lifecycle;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderCollection;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use function str_replace;

class Uninstaller
{
    /**
     * @var EntityRepositoryInterface
     */
    private $mediaFolderRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $mediaRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $mediaDefaultFolderRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $mediaFolderConfigRepository;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        EntityRepositoryInterface $mediaFolderRepository,
        EntityRepositoryInterface $mediaRepository,
        EntityRepositoryInterface $mediaDefaultFolderRepository,
        EntityRepositoryInterface $mediaFolderConfigRepository,
        Connection $connection
    ) {
        $this->mediaFolderRepository = $mediaFolderRepository;
        $this->mediaRepository = $mediaRepository;
        $this->mediaDefaultFolderRepository = $mediaDefaultFolderRepository;
        $this->mediaFolderConfigRepository = $mediaFolderConfigRepository;
        $this->connection = $connection;
    }

    public function uninstall(Context $context): void
    {
        $this->removeTemplateTables();

        $mediaFolders = $this->getMediaFolders($context);
        if ($mediaFolders->count() <= 0) {
            return;
        }

        foreach ($mediaFolders as $mediaFolder) {
            $this->removeTemplateMedia($mediaFolder, $context);
            $this->removeDefaultMediaFolder($mediaFolder, $context);
            $this->removeMediaFolder($mediaFolder, $context);
            $this->removeMediaFolderConfig($mediaFolder, $context);
        }
    }

    private function getMediaFolders(Context $context): MediaFolderCollection
    {
        $criteria = new Criteria();
        $criteria->addAssociation('defaultFolder');
        $criteria->addAssociation('media');
        $criteria->addFilter(
            new MultiFilter(
                MultiFilter::CONNECTION_OR,
                [
                    new EqualsFilter(
                        'media_folder.defaultFolder.entity',
                        'swag_customized_products_template'
                    ),
                    new EqualsFilter(
                        'media_folder.defaultFolder.entity',
                        'swag_customized_products_template_storefront_upload'
                    ),
                ]
            )
        );

        /** @var MediaFolderCollection $mediaFolderCollection */
        $mediaFolderCollection = $this->mediaFolderRepository->search($criteria, $context)->getEntities();

        return $mediaFolderCollection;
    }

    private function removeTemplateMedia(MediaFolderEntity $mediaFolder, Context $context): void
    {
        $mediaIds = [];
        foreach ($mediaFolder->getMedia() as $media) {
            $mediaIds[] = ['id' => $media->getId()];
        }

        if (!empty($mediaIds)) {
            $this->mediaRepository->delete($mediaIds, $context);
        }
    }

    private function removeDefaultMediaFolder(MediaFolderEntity $mediaFolder, Context $context): void
    {
        $this->mediaDefaultFolderRepository->delete([['id' => $mediaFolder->getDefaultFolderId()]], $context);
    }

    private function removeMediaFolder(MediaFolderEntity $mediaFolder, Context $context): void
    {
        $this->mediaFolderRepository->delete([['id' => $mediaFolder->getId()]], $context);
    }

    private function removeMediaFolderConfig(MediaFolderEntity $mediaFolder, Context $context): void
    {
        $this->mediaFolderConfigRepository->delete([['id' => $mediaFolder->getConfigurationId()]], $context);
    }

    private function removeTemplateTables(): void
    {
        $this->connection->executeUpdate('DROP TABLE IF EXISTS `swag_customized_products_template_exclusion_condition_values`;');
        $this->connection->executeUpdate('DROP TABLE IF EXISTS `swag_customized_products_template_exclusion_condition`;');
        $this->connection->executeUpdate('DROP TABLE IF EXISTS `swag_customized_products_template_exclusion_operator_translation`;');
        $this->connection->executeUpdate('DROP TABLE IF EXISTS `swag_customized_products_template_exclusion_operator`;');
        $this->connection->executeUpdate('DROP TABLE IF EXISTS `swag_customized_products_template_exclusion`;');
        $this->connection->executeUpdate('DROP TABLE IF EXISTS `swag_customized_products_template_option_value_price`;');
        $this->connection->executeUpdate('DROP TABLE IF EXISTS `swag_customized_products_template_option_value_translation`;');
        $this->connection->executeUpdate('DROP TABLE IF EXISTS `swag_customized_products_template_option_value`;');
        $this->connection->executeUpdate('DROP TABLE IF EXISTS `swag_customized_products_template_option_price`;');
        $this->connection->executeUpdate('DROP TABLE IF EXISTS `swag_customized_products_template_option_translation`;');
        $this->connection->executeUpdate('DROP TABLE IF EXISTS `swag_customized_products_template_option`;');
        $this->connection->executeUpdate('DROP TABLE IF EXISTS `swag_customized_products_template_exclusion`;');
        $this->connection->executeUpdate('DROP TABLE IF EXISTS `swag_customized_products_template_translation`;');
        $this->connection->executeUpdate('DROP TABLE IF EXISTS `swag_customized_products_template`;');

        if ($this->checkIfColumnExist(ProductDefinition::ENTITY_NAME, Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_FK_COLUMN)) {
            $this->dropColumn(ProductDefinition::ENTITY_NAME, Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_FK_COLUMN);
        }

        if ($this->checkIfColumnExist(ProductDefinition::ENTITY_NAME, Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN)) {
            $this->dropColumn(ProductDefinition::ENTITY_NAME, Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);
        }

        if ($this->checkIfColumnExist(ProductDefinition::ENTITY_NAME, Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_REFERENCE_VERSION_COLUMN)) {
            $this->dropColumn(ProductDefinition::ENTITY_NAME, Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_REFERENCE_VERSION_COLUMN);
        }
    }

    private function checkIfColumnExist(string $tableName, string $columnName): bool
    {
        $sql = <<<SQL
SELECT column_name
FROM information_schema.columns
WHERE table_name = :tableName
    AND column_name = :columnName
    AND table_schema = DATABASE();
SQL;

        $columnNameInDb = $this->connection->executeQuery(
            $sql,
            ['tableName' => $tableName, 'columnName' => $columnName]
        )->fetchColumn();

        return $columnNameInDb === $columnName;
    }

    private function dropColumn(string $tableName, string $columnName): void
    {
        $sql = str_replace(
            ['#table#', '#column#'],
            [$tableName, $columnName],
            'ALTER TABLE `#table#` DROP COLUMN `#column#`'
        );
        $this->connection->executeUpdate($sql);
    }
}
