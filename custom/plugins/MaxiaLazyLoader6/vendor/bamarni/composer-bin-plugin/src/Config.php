<?php

namespace _PhpScoper833c86d6963f\Bamarni\Composer\Bin;

use _PhpScoper833c86d6963f\Composer\Composer;
final class Config
{
    private $config;
    public function __construct(\_PhpScoper833c86d6963f\Composer\Composer $composer)
    {
        $extra = $composer->getPackage()->getExtra();
        $this->config = \array_merge(['bin-links' => \true, 'target-directory' => 'vendor-bin'], isset($extra['bamarni-bin']) ? $extra['bamarni-bin'] : []);
    }
    /**
     * @return bool
     */
    public function binLinksAreEnabled()
    {
        return \true === $this->config['bin-links'];
    }
    /**
     * @return string
     */
    public function getTargetDirectory()
    {
        return $this->config['target-directory'];
    }
}
