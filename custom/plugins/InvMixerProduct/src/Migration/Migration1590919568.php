<?php declare(strict_types=1);

namespace InvMixerProduct\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * Class Migration1590919568
 * @package InvMixerProduct\Migration
 */
class Migration1590919568 extends MigrationStep
{
    /**
     * @return int
     */
    public function getCreationTimestamp(): int
    {
        return 1590919568;
    }

    /**
     * @param Connection $connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public function update(Connection $connection): void
    {
        $sql = <<<SQL

create table inv_mixer_product__mix
(
	id binary(16) null,
	created_at DATETIME(3) not null,
	updated_at DATETIME(3) null,
	label varchar(255) null,
	container_definition JSON null,
	customer_id binary(16) null,
	constraint inv_mixer_product__mix_customer_id_fk
		foreign key (customer_id) references customer (id)
			on delete set null
);

create unique index inv_mixer_product__mix_id_uindex
	on inv_mixer_product__mix (id);

alter table inv_mixer_product__mix
	add constraint inv_mixer_product__mix_pk
		primary key (id);


SQL;

        $connection->executeUpdate($sql);

    }

    /**
     * @param Connection $connection
     */
    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
