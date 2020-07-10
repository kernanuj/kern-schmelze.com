<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1565933910TemplateProduct extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public const PRODUCT_TEMPLATE_FK_COLUMN = 'swag_customized_products_template_id';
    public const PRODUCT_TEMPLATE_INHERITANCE_COLUMN = 'swagCustomizedProductsTemplate';
    public const PRODUCT_TEMPLATE_REFERENCE_VERSION_COLUMN = 'swag_customized_products_template_version_id';

    public function getCreationTimestamp(): int
    {
        return 1565933910;
    }

    public function update(Connection $connection): void
    {
        $fields = [
            self::PRODUCT_TEMPLATE_FK_COLUMN,
            self::PRODUCT_TEMPLATE_REFERENCE_VERSION_COLUMN,
        ];

        foreach ($fields as $field) {
            $sql = \str_replace(
                ['#table#', '#column#'],
                [ProductDefinition::ENTITY_NAME, $field],
                'ALTER TABLE `#table#` ADD COLUMN `#column#` binary(16) NULL'
            );
            $connection->executeUpdate($sql);
        }

        $this->updateInheritance($connection, ProductDefinition::ENTITY_NAME, self::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
