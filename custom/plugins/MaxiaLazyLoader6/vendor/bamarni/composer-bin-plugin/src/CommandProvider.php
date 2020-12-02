<?php

namespace _PhpScoperfd240ab1f7e6\Bamarni\Composer\Bin;

use _PhpScoperfd240ab1f7e6\Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
class CommandProvider implements \_PhpScoperfd240ab1f7e6\Composer\Plugin\Capability\CommandProvider
{
    /**
     * {@inheritDoc}
     */
    public function getCommands()
    {
        return [new \_PhpScoperfd240ab1f7e6\Bamarni\Composer\Bin\BinCommand()];
    }
}
