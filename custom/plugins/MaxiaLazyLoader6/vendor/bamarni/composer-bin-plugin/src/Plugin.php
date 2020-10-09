<?php

namespace _PhpScoper833c86d6963f\Bamarni\Composer\Bin;

use _PhpScoper833c86d6963f\Composer\Composer;
use _PhpScoper833c86d6963f\Composer\IO\IOInterface;
use _PhpScoper833c86d6963f\Composer\Plugin\PluginInterface;
use _PhpScoper833c86d6963f\Composer\Plugin\Capable;
class Plugin implements \_PhpScoper833c86d6963f\Composer\Plugin\PluginInterface, \_PhpScoper833c86d6963f\Composer\Plugin\Capable
{
    /**
     * {@inheritDoc}
     */
    public function activate(\_PhpScoper833c86d6963f\Composer\Composer $composer, \_PhpScoper833c86d6963f\Composer\IO\IOInterface $io)
    {
    }
    /**
     * {@inheritDoc}
     */
    public function getCapabilities()
    {
        return ['_PhpScoper833c86d6963f\\Composer\\Plugin\\Capability\\CommandProvider' => '_PhpScoper833c86d6963f\\Bamarni\\Composer\\Bin\\CommandProvider'];
    }
    /**
     * {@inheritDoc}
     */
    public function deactivate(\_PhpScoper833c86d6963f\Composer\Composer $composer, \_PhpScoper833c86d6963f\Composer\IO\IOInterface $io)
    {
    }
    /**
     * {@inheritDoc}
     */
    public function uninstall(\_PhpScoper833c86d6963f\Composer\Composer $composer, \_PhpScoper833c86d6963f\Composer\IO\IOInterface $io)
    {
    }
}
