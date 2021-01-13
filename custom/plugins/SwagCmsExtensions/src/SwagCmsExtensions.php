<?php
declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Swag\CmsExtensions\Util\Lifecycle\Uninstaller;

class SwagCmsExtensions extends Plugin
{
    private const SWAG_CMS_EXTENSIONS_QUICKVIEW_PRIVILEGE_KEY = 'swag_cms_extensions_quickview:';
    private const SWAG_CMS_EXTENSIONS_SCROLL_NAVIGATION_PRIVILEGE_KEY = 'swag_cms_extensions_scroll_navigation:';
    private const SWAG_CMS_EXTENSIONS_SCROLL_NAVIGATION_PAGE_SETTINGS_PRIVILEGE_KEY = 'swag_cms_extensions_scroll_navigation_page_settings:';

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        (new Uninstaller(
            $uninstallContext,
            $connection
        ))->uninstall();
    }

    public function update(UpdateContext $updateContext): void
    {
        parent::update($updateContext);

        $this->addCustomPrivileges();
    }

    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);

        $this->addCustomPrivileges();
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        parent::deactivate($deactivateContext);

        $this->removeCustomPrivileges();
    }

    public function enrichPrivileges(): array
    {
        return [
            'cms.viewer' => [
                self::SWAG_CMS_EXTENSIONS_QUICKVIEW_PRIVILEGE_KEY . 'read',
                self::SWAG_CMS_EXTENSIONS_SCROLL_NAVIGATION_PRIVILEGE_KEY . 'read',
                self::SWAG_CMS_EXTENSIONS_SCROLL_NAVIGATION_PAGE_SETTINGS_PRIVILEGE_KEY . 'read',
            ],
            'cms.editor' => [
                self::SWAG_CMS_EXTENSIONS_QUICKVIEW_PRIVILEGE_KEY . 'update',
                self::SWAG_CMS_EXTENSIONS_SCROLL_NAVIGATION_PRIVILEGE_KEY . 'update',
                self::SWAG_CMS_EXTENSIONS_SCROLL_NAVIGATION_PAGE_SETTINGS_PRIVILEGE_KEY . 'update',
            ],
            'cms.creator' => [
                self::SWAG_CMS_EXTENSIONS_QUICKVIEW_PRIVILEGE_KEY . 'create',
                self::SWAG_CMS_EXTENSIONS_SCROLL_NAVIGATION_PRIVILEGE_KEY . 'create',
                self::SWAG_CMS_EXTENSIONS_SCROLL_NAVIGATION_PAGE_SETTINGS_PRIVILEGE_KEY . 'create',
            ],
            'cms.deleter' => [
                self::SWAG_CMS_EXTENSIONS_QUICKVIEW_PRIVILEGE_KEY . 'delete',
                self::SWAG_CMS_EXTENSIONS_SCROLL_NAVIGATION_PRIVILEGE_KEY . 'delete',
                self::SWAG_CMS_EXTENSIONS_SCROLL_NAVIGATION_PAGE_SETTINGS_PRIVILEGE_KEY . 'delete',
            ],
        ];
    }

    private function addCustomPrivileges(): void
    {
        if (!\method_exists($this, 'addPrivileges') || \method_exists(parent::class, 'enrichPrivileges')) {
            return;
        }

        foreach ($this->enrichPrivileges() as $role => $privileges) {
            $this->addPrivileges($role, $privileges);
        }
    }

    private function removeCustomPrivileges(): void
    {
        if (!\method_exists($this, 'removePrivileges')) {
            return;
        }

        $privilegesToDelete = [];
        foreach ($this->enrichPrivileges() as $privileges) {
            foreach ($privileges as $privilege) {
                $privilegesToDelete[] = $privilege;
            }
        }

        $this->removePrivileges($privilegesToDelete);
    }
}
