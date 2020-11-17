<?php declare(strict_types=1);

namespace CoeFacebookPixelSw6;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;

/**
 * Class CoeFacebookPixelSw6
 * @package CoeFacebookPixelSw6
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
class CoeFacebookPixelSw6 extends Plugin {

    /**
     * @param InstallContext $context
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    public function install(InstallContext $context): void
    {
        parent::install($context);
    }

    /**
     * @param UninstallContext $context
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }
    }
}