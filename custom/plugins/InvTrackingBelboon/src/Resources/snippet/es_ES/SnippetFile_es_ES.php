<?php declare(strict_types=1);

namespace InvTrackingBelboon\Resources\snippet\es_ES;

use Shopware\Core\System\Snippet\Files\SnippetFileInterface;

class SnippetFile_frFR implements SnippetFileInterface
{
    public function getName(): string
    {
        return 'storefront.es-ES';
    }

    public function getPath(): string
    {
        return __DIR__ . '/storefront.es-ES.json';
    }

    public function getIso(): string
    {
        return 'es-ES';
    }

    public function getAuthor(): string
    {
        return 'Nils Harder | inventivo.de';
    }

    public function isBase(): bool
    {
        return false;
    }
}
