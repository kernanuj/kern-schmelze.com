<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1582119202TemplateOptionPriceRemoveTypeProperties extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1582119202;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
ALTER TABLE `swag_customized_products_template_option_value_price` DROP COLUMN `type_properties`;
SQL;

        $connection->exec($query);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
