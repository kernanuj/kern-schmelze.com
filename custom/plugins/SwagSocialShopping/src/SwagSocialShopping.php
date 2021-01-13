<?php

declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagSocialShopping;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use SwagSocialShopping\Installer\CustomFieldInstaller;
use SwagSocialShopping\Installer\SalesChannelInstaller;

class SwagSocialShopping extends Plugin
{
    public const SALES_CHANNEL_TYPE_SOCIAL_SHOPPING = '9ce0868f406d47d98cfe4b281e62f098';
    public const SOCIAL_SHOPPING_SALES_CHANNEL_WRITTEN_EVENT = 'swag_social_shopping_sales_channel.written';

    private const SWAG_SOCIAL_SHOPPING_SALES_CHANNEL_PRIVILEGE_KEY = 'swag_social_shopping_sales_channel:';
    private const SWAG_SOCIAL_SHOPPING_PRODUCT_ERROR_PRIVILEGE_KEY = 'swag_social_shopping_product_error:';

    public function activate(ActivateContext $activateContext): void
    {
        /** @var EntityRepositoryInterface $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        (new CustomFieldInstaller($customFieldSetRepository))->activate($activateContext);
        (new SalesChannelInstaller($this->container))->activate($activateContext);

        $this->addCustomPrivileges();

        parent::activate($activateContext);
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        /** @var EntityRepositoryInterface $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        (new CustomFieldInstaller($customFieldSetRepository))->deactivate($deactivateContext);
        (new SalesChannelInstaller($this->container))->deactivate($deactivateContext);

        $this->removeCustomPrivileges();

        parent::deactivate($deactivateContext);
    }

    public function install(InstallContext $installContext): void
    {
        /** @var EntityRepositoryInterface $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        (new CustomFieldInstaller($customFieldSetRepository))->install($installContext);
        (new SalesChannelInstaller($this->container))->install($installContext);

        parent::install($installContext);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        /** @var EntityRepositoryInterface $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        (new CustomFieldInstaller($customFieldSetRepository))->uninstall($uninstallContext);
        (new SalesChannelInstaller($this->container))->uninstall($uninstallContext);

        parent::uninstall($uninstallContext);
    }

    public function update(UpdateContext $updateContext): void
    {
        /** @var EntityRepositoryInterface $customFieldSetRepository */
        $customFieldSetRepository = $this->container->get('custom_field_set.repository');
        (new CustomFieldInstaller($customFieldSetRepository))->update($updateContext);
        (new SalesChannelInstaller($this->container))->update($updateContext);

        $this->addCustomPrivileges();

        parent::update($updateContext);
    }

    private function addCustomPrivileges(): void
    {
        if (!\method_exists($this, 'addPrivileges')) {
            return;
        }

        foreach ($this->getCustomPrivileges() as $role => $privileges) {
            $this->addPrivileges($role, $privileges);
        }
    }

    private function removeCustomPrivileges(): void
    {
        if (!\method_exists($this, 'removePrivileges')) {
            return;
        }

        $privilegesToRemove = [];
        foreach ($this->getCustomPrivileges() as $privileges) {
            foreach ($privileges as $privilege) {
                if (\mb_strpos($privilege, self::SWAG_SOCIAL_SHOPPING_PRODUCT_ERROR_PRIVILEGE_KEY) !== 0
                    && \mb_strpos($privilege, self::SWAG_SOCIAL_SHOPPING_SALES_CHANNEL_PRIVILEGE_KEY) !== 0
                ) {
                    continue;
                }

                $privilegesToRemove[] = $privilege;
            }
        }

        $this->removePrivileges($privilegesToRemove);
    }

    private function getCustomPrivileges(): array
    {
        return [
            'sales_channel.viewer' => [
                self::SWAG_SOCIAL_SHOPPING_SALES_CHANNEL_PRIVILEGE_KEY . 'read',
                self::SWAG_SOCIAL_SHOPPING_PRODUCT_ERROR_PRIVILEGE_KEY . 'read',
                'seo_url:read',
                'product_visibility:read',
            ],
            'sales_channel.editor' => [
                self::SWAG_SOCIAL_SHOPPING_SALES_CHANNEL_PRIVILEGE_KEY . 'update',
            ],
            'sales_channel.creator' => [
                self::SWAG_SOCIAL_SHOPPING_SALES_CHANNEL_PRIVILEGE_KEY . 'create',
            ],
        ];
    }
}
