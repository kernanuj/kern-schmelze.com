<?php declare(strict_types=1);

namespace Maxia\MaxiaLazyLoader6\Components;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class Config
{
    /**
     * @var SystemConfigService
     */
    protected $configService;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var array
     */
    public $config = null;

    /**
     * @param SystemConfigService $configService
     * @param string $namespace
     */
    public function __construct(SystemConfigService $configService, string $namespace)
    {
        $this->configService = $configService;
        $this->namespace = $namespace;
    }

    /**
     * @return array
     */
    public function getConfig() : array
    {
        /** @var array $config */
        $config = $this->configService->get($this->namespace);

        $config['active'] = true;
        $config['expand'] = (isset($config['expand']) && !empty($config['expand'])) ? $config['expand'] : 1;
        $config['debugLogging'] = isset($config['debugLogging']) ? $config['debugLogging'] : false;

        // parse custom blacklist selectors into array
        if (isset($config['blacklistSelectors']) && !empty($config['blacklistSelectors'])) {

            $selectors = explode("\n", $config['blacklistSelectors']);

            foreach ($selectors as $index => $line) {
                $line = trim($selectors[$index]);

                if (empty($line)) {
                    unset($selectors[$index]);
                }
            }

            $config['blacklistSelectors'] = $selectors;

        } else {
            $config['blacklistSelectors'] = [];
        }

        $defaultSelectors = ['.product-detail-media', '.zoom-modal-wrapper'];

        foreach ($defaultSelectors as $selector) {
            if (!in_array($selector, $config['blacklistSelectors'])) {
                $config['blacklistSelectors'][] = $selector;
            }
        }

        // parse excluded urls
        if (isset($config['blacklistUrls']) && !empty($config['blacklistUrls'])) {
            $config['blacklistUrls'] = explode("\n", $config['blacklistUrls']);

            foreach ($config['blacklistUrls'] as $index => &$expr) {
                $expr = trim($expr);

                if (empty($expr) || strpos($expr, '#') === 0) {
                    unset($config['blacklistUrls'][$index]);
                    continue;
                }

                $expr = str_replace("\\*", '.*', preg_quote($expr, '#'));
            }
        }

        return $config;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if ($this->config === null) {
            $this->config = $this->getConfig();
        }

        return isset($this->config[$key])
            ? $this->config[$key]
            : null;
    }
}