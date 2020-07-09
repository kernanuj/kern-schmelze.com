<?php

declare(strict_types=1);

namespace KlarnaPayment;

use Doctrine\DBAL\Connection;
use KlarnaPayment\Installer\ConfigInstaller;
use KlarnaPayment\Installer\CustomFieldInstaller;
use KlarnaPayment\Installer\PaymentMethodInstaller;
use KlarnaPayment\Installer\RuleInstaller;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class KlarnaPayment extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection'));
        $loader->load('services.xml');

        parent::build($container);
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $installContext): void
    {
        (new PaymentMethodInstaller($this->container))->install($installContext);
        (new CustomFieldInstaller($this->container))->install($installContext);
        (new ConfigInstaller($this->container))->install($installContext);
        (new RuleInstaller($this->container))->install($installContext);
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $updateContext): void
    {
        (new PaymentMethodInstaller($this->container))->update($updateContext);
        (new CustomFieldInstaller($this->container))->update($updateContext);
        (new ConfigInstaller($this->container))->update($updateContext);
        (new RuleInstaller($this->container))->update($updateContext);
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $activateContext): void
    {
        (new PaymentMethodInstaller($this->container))->activate($activateContext);
        (new CustomFieldInstaller($this->container))->activate($activateContext);
        (new ConfigInstaller($this->container))->activate($activateContext);
        (new RuleInstaller($this->container))->activate($activateContext);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $deactivateContext): void
    {
        (new PaymentMethodInstaller($this->container))->deactivate($deactivateContext);
        (new CustomFieldInstaller($this->container))->deactivate($deactivateContext);
        (new ConfigInstaller($this->container))->deactivate($deactivateContext);
        (new RuleInstaller($this->container))->deactivate($deactivateContext);
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        (new PaymentMethodInstaller($this->container))->uninstall($uninstallContext);
        (new CustomFieldInstaller($this->container))->uninstall($uninstallContext);
        (new ConfigInstaller($this->container))->uninstall($uninstallContext);
        (new RuleInstaller($this->container))->uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);
        $connection->exec('DROP TABLE IF EXISTS klarna_payment_request_log');
        $connection->exec('DROP TABLE IF EXISTS klarna_payment_button_key');
    }
}
