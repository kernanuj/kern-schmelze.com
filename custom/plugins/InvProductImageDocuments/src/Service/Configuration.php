<?php
namespace InvProductImageDocuments\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Class Configuration
 *
 * @author Nils Harder <hallo@inventivo.de>
 * @copyright Copyright (c) 2020 Nils Harder
 * @package InvProductImageDocuments\Service
 * @version   1
 */
class Configuration
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public const INV_CONFIG_DOMAIN = 'InvProductImageDocuments.config.';

    public function __construct(SystemConfigService $systemConfigService) {
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * @param $key
     * @return bool|mixed
     * @throws \Exception
     */
    public function getPluginConfig($key)
    {
        return $this->systemConfigService->get(self::INV_CONFIG_DOMAIN . $key);
    }
}
