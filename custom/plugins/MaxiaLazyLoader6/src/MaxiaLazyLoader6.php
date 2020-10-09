<?php declare(strict_types=1);

namespace Maxia\MaxiaLazyLoader6;

use Shopware\Core\Framework\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MaxiaLazyLoader6 extends Plugin
{
    public function build(ContainerBuilder $container): void
    {
        $container->setParameter('maxia.maxia_lazy_loader_6.plugin_name', 'LazyLoader6');
        parent::build($container);
    }

    public function boot(): void
    {
        // register composer autoloader
        parent::boot();
        $path = $this->getBasePath() . '/vendor/autoload.php';

        if (file_exists($path)) {
            require_once $path;
        }
    }
}