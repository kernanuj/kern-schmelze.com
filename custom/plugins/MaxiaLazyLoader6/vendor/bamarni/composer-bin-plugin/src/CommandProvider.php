<?php

namespace _PhpScoper833c86d6963f\Bamarni\Composer\Bin;

use _PhpScoper833c86d6963f\Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
class CommandProvider implements \_PhpScoper833c86d6963f\Composer\Plugin\Capability\CommandProvider
{
    /**
     * {@inheritDoc}
     */
    public function getCommands()
    {
        return [new \_PhpScoper833c86d6963f\Bamarni\Composer\Bin\BinCommand()];
    }
}
