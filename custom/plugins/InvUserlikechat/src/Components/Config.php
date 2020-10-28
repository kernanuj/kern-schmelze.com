<?php declare(strict_types=1);

namespace InvUserlikechat\Components;

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

        $config['status'] = isset($config['status']) ? $config['status'] : false;

        // parse excluded urls
        if (isset($config['noDisplayUrls']) && !empty($config['noDisplayUrls'])) {
            $config['noDisplayUrls'] = explode("\n", $config['noDisplayUrls']);

            foreach ($config['noDisplayUrls'] as $index => &$expr) {
                $expr = trim($expr);

                if (substr($expr, 0, 1) === '#') {
                    unset($config['noDisplayUrls'][$index]);
                    continue;
                }

                $expr = str_replace("\\*", '.*', preg_quote($expr, '#'));
            }
        }

        // parse only display  urls
        if (isset($config['onlyDisplayUrls']) && !empty($config['onlyDisplayUrls'])) {
            $config['onlyDisplayUrls'] = explode("\n", $config['onlyDisplayUrls']);

            foreach ($config['onlyDisplayUrls'] as $index => &$exprOnly) {
                $exprOnly = trim($exprOnly);

                if (substr($exprOnly, 0, 1) === '#') {
                    unset($config['onlyDisplayUrls'][$index]);
                    continue;
                }

                $exprOnly = str_replace("\\*", '.*', preg_quote($exprOnly, '#'));
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
