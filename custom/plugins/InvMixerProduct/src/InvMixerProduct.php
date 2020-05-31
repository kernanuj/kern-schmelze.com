<?php declare(strict_types=1);

namespace InvMixerProduct;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;

class InvMixerProduct extends Plugin
{

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\DBALException
     */
    public function uninstall(Plugin\Context\UninstallContext $context): void
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);
        $connection->exec('DROP TABLE IF EXISTS inv_mixer_product__mix');
    }
}
