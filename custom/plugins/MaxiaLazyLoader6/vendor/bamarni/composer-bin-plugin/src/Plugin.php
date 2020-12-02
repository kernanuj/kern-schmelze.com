<?php

namespace _PhpScoperfd240ab1f7e6\Bamarni\Composer\Bin;

use _PhpScoperfd240ab1f7e6\Composer\Composer;
use _PhpScoperfd240ab1f7e6\Composer\IO\IOInterface;
use _PhpScoperfd240ab1f7e6\Composer\Plugin\PluginInterface;
use _PhpScoperfd240ab1f7e6\Composer\Plugin\Capable;
class Plugin implements \_PhpScoperfd240ab1f7e6\Composer\Plugin\PluginInterface, \_PhpScoperfd240ab1f7e6\Composer\Plugin\Capable
{
    /**
     * {@inheritDoc}
     */
    public function activate(\_PhpScoperfd240ab1f7e6\Composer\Composer $composer, \_PhpScoperfd240ab1f7e6\Composer\IO\IOInterface $io)
    {
    }
    /**
     * {@inheritDoc}
     */
    public function getCapabilities()
    {
        return ['_PhpScoperfd240ab1f7e6\\Composer\\Plugin\\Capability\\CommandProvider' => '_PhpScoperfd240ab1f7e6\\Bamarni\\Composer\\Bin\\CommandProvider'];
    }
    /**
     * {@inheritDoc}
     */
    public function deactivate(\_PhpScoperfd240ab1f7e6\Composer\Composer $composer, \_PhpScoperfd240ab1f7e6\Composer\IO\IOInterface $io)
    {
    }
    /**
     * {@inheritDoc}
     */
    public function uninstall(\_PhpScoperfd240ab1f7e6\Composer\Composer $composer, \_PhpScoperfd240ab1f7e6\Composer\IO\IOInterface $io)
    {
    }
}
