<?php

declare(strict_types=1);

namespace KlarnaPayment\Installer;

use KlarnaPayment\Components\ConfigReader\ConfigReaderInterface;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfigInstaller implements InstallerInterface
{
    private const DEFAULT_VALUES = [
        'allowedKlarnaPaymentsCodes'         => ['pay_now', 'pay_later', 'pay_over_time'],
        'kpSendExtraMerchantData'            => true,
        'enableCorporateCustomerIntegration' => true,
        'externalPaymentMethods'             => [],
        'externalCheckouts'                  => [],
        'automaticRefund'                    => 'deactivated',
        'automaticCapture'                   => 'deactivated',
        'testMode'                           => true,
        'kpDisplayFooterBadge'               => true,
        'kcoDisplayFooterBadge'              => true,
        'kcoFooterBadgeStyle'                => 'long-blue',
        'isInitialized'                      => false,
        'klarnaType'                         => 'deactivated',
        'instantShoppingVariation'           => 'dark',
        'instantShoppingType'                => 'buy',
    ];

    /** @var SystemConfigService */
    private $systemConfigService;

    public function __construct(ContainerInterface $container)
    {
        $this->systemConfigService = $container->get(SystemConfigService::class);
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $context): void
    {
        if (empty(self::DEFAULT_VALUES)) {
            return;
        }

        $this->setDefaultValues();
    }

    /**
     * {@inheritdoc}
     */
    public function update(UpdateContext $context): void
    {
        if (empty(self::DEFAULT_VALUES)) {
            return;
        }

        $this->setDefaultValues();
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(UninstallContext $context): void
    {
        // Nothing to do here
    }

    /**
     * {@inheritdoc}
     */
    public function activate(ActivateContext $context): void
    {
        // Nothing to do here
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(DeactivateContext $context): void
    {
        // Nothing to do here
    }

    private function setDefaultValues(): void
    {
        foreach (self::DEFAULT_VALUES as $key => $value) {
            $configKey = ConfigReaderInterface::SYSTEM_CONFIG_DOMAIN . $key;

            $currentValue = $this->systemConfigService->get($configKey);

            if ($currentValue !== null) {
                continue;
            }

            $this->systemConfigService->set($configKey, $value);
        }
    }
}
