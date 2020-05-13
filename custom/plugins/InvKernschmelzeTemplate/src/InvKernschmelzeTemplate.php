<?php declare(strict_types=1);

namespace InvKernschmelzeTemplate;

use Shopware\Core\Framework\Plugin;
use Shopware\Storefront\Framework\ThemeInterface;

class InvKernschmelzeTemplate extends Plugin implements ThemeInterface
{
    public function getThemeConfigPath(): string
    {
        return 'theme.json';
    }
}