<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;
use Swag\CustomizedProducts\Template\TemplateDefinition;

class Migration1594288127AddOptionsAutoCollapseSettings extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1594288127;
    }

    public function update(Connection $connection): void
    {
        $queryString = '
            ALTER TABLE `#table#`
                ADD COLUMN `options_auto_collapse` TINYINT(1) NOT NULL DEFAULT 0
                AFTER `confirm_input`;
        ';

        $sql = \str_replace(
            ['#table#'],
            [TemplateDefinition::ENTITY_NAME],
            $queryString
        );
        $connection->executeUpdate($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
