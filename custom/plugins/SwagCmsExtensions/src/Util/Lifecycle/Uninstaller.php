<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Util\Lifecycle;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Swag\CmsExtensions\Quickview\QuickviewDefinition;
use Swag\CmsExtensions\ScrollNavigation\Aggregate\ScrollNavigationPageSettings\ScrollNavigationPageSettingsDefinition;
use Swag\CmsExtensions\ScrollNavigation\Aggregate\ScrollNavigationTranslation\ScrollNavigationTranslationDefinition;
use Swag\CmsExtensions\ScrollNavigation\ScrollNavigationDefinition;

class Uninstaller
{
    /**
     * @var UninstallContext
     */
    private $context;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(UninstallContext $context, Connection $connection)
    {
        $this->context = $context;
        $this->connection = $connection;
    }

    public function uninstall(): void
    {
        if ($this->context->keepUserData()) {
            return;
        }

        $classNames = [
            QuickviewDefinition::ENTITY_NAME,
            ScrollNavigationPageSettingsDefinition::ENTITY_NAME,
            ScrollNavigationTranslationDefinition::ENTITY_NAME,
            ScrollNavigationDefinition::ENTITY_NAME,
        ];

        foreach ($classNames as $className) {
            $this->connection->executeUpdate(sprintf('DROP TABLE IF EXISTS `%s`', $className));
        }
    }
}
