<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Swag\CustomizedProducts\Util\Lifecycle\Uninstaller;

class SwagCustomizedProducts extends Plugin
{
    public const CURRENT_API_VERSION = 2;

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        /** @var EntityRepositoryInterface $mediaFolderRepository */
        $mediaFolderRepository = $this->container->get('media_folder.repository');
        /** @var EntityRepositoryInterface $mediaRepository */
        $mediaRepository = $this->container->get('media.repository');
        /** @var EntityRepositoryInterface $mediaDefaultFolderRepository */
        $mediaDefaultFolderRepository = $this->container->get('media_default_folder.repository');
        /** @var EntityRepositoryInterface $mediaFolderConfigRepository */
        $mediaFolderConfigRepository = $this->container->get('media_folder_configuration.repository');
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        $uninstaller = new Uninstaller(
            $mediaFolderRepository,
            $mediaRepository,
            $mediaDefaultFolderRepository,
            $mediaFolderConfigRepository,
            $connection
        );
        $uninstaller->uninstall($uninstallContext->getContext());
    }
}
