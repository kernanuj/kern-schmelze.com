<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1580300846ModifyTemplateOptionValuePriceConstraint extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1580300846;
    }

    public function update(Connection $connection): void
    {
        if ($this->isConstraintExisting($connection)) {
            $this->removeForeignKey($connection);
            $this->removeUniqueConstraint($connection);
        }

        $this->addNewUniqueConstraint($connection);
        $this->reAddForeignKey($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function isConstraintExisting(Connection $connection): bool
    {
        $exists = $connection->executeQuery(
            <<<SQL
        SELECT CONSTRAINT_NAME  FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_NAME = 'uniq.swag_cupr_template_option_value_id__version'
        AND TABLE_NAME = 'swag_customized_products_template_option_value_price'
SQL
        )->fetch(\PDO::FETCH_ASSOC);

        return (bool) $exists;
    }

    private function removeForeignKey(Connection $connection): void
    {
        $connection->executeQuery(
            'ALTER TABLE `swag_customized_products_template_option_value_price`
                    DROP FOREIGN KEY `fk.swag_cuprtemop_value_price.template_option_value_id`;'
        );
    }

    private function removeUniqueConstraint(Connection $connection): void
    {
        $connection->executeQuery(
            'ALTER TABLE `swag_customized_products_template_option_value_price`
                    DROP INDEX `uniq.swag_cupr_template_option_value_id__version`;'
        );
    }

    private function addNewUniqueConstraint(Connection $connection): void
    {
        $connection->executeQuery(
            'ALTER TABLE `swag_customized_products_template_option_value_price`
                    ADD CONSTRAINT `uniq.swag_cupr_template_option_value_id__version`
                        UNIQUE (`template_option_value_id`, `template_option_value_version_id`, `rule_id`);'
        );
    }

    private function reAddForeignKey(Connection $connection): void
    {
        $connection->executeQuery(
            'ALTER TABLE `swag_customized_products_template_option_value_price`
                    ADD FOREIGN KEY  `fk.swag_cuprtemop_value_price.template_option_value_id`(`template_option_value_id`, `template_option_value_version_id`)
                    REFERENCES `swag_customized_products_template_option_value` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE'
        );
    }
}
