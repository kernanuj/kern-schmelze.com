<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CmsExtensions\Quickview\QuickviewDefinition;

class Migration1575555505EnableQuickviewForDefaultLayouts extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1575555505;
    }

    public function update(Connection $connection): void
    {
        $productBlocks = $connection->fetchAll("SELECT `id` FROM `cms_block` WHERE `type` IN ('product-listing', 'product-slider', 'product-three-column')");
        $quickviewSettingPresent = $connection->fetchAll(\sprintf('SELECT `cms_block_id` AS `id` FROM `%s`', QuickviewDefinition::ENTITY_NAME));

        foreach ($productBlocks as $block) {
            if (\in_array($block, $quickviewSettingPresent, true)) {
                continue;
            }

            $quickview = [
                'id' => Uuid::randomBytes(),
                'cms_block_id' => $block['id'],
                'active' => 1,
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ];

            $connection->insert('swag_cms_extensions_quickview', $quickview);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
