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
        $config = $this->configService->get($this->namespace);

        $config['active'] = true;
        $config['expand'] = (isset($config['expand']) && !empty($config['expand'])) ? $config['expand'] : 1;
        $config['debugLogging'] = isset($config['debugLogging']) ? $config['debugLogging'] : false;

        // parse custom blacklist selectors into array
        if (isset($config['blacklistSelectors']) && !empty($config['blacklistSelectors'])) {

            $selectors = explode("\n", $config['blacklistSelectors']);

            foreach ($selectors as &$line) {
                $line = trim($line);

                if (empty($line)) {
                    unset($line);
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

            foreach ($config['blacklistUrls'] as $index => &$url) {
                $url = trim($url);

                if (substr($url, 0, 1) == '#') {
                    unset($config['blacklistUrls'][$index]);
                    continue;
                }

                $url = str_replace('*', '.*', $url);
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