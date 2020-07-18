<?php declare(strict_types=1);

namespace InvMixerProduct\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1595066829 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1595066829;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
alter table inv_mixer_product__mix
	add display_id int;

create unique index inv_mixer_product__mix_display_id_uindex
	on inv_mixer_product__mix (display_id);
SQL;

        $connection->exec($sql);

    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
