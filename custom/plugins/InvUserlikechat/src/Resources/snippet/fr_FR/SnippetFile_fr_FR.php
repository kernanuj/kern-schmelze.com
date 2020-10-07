<?php declare(strict_types=1);

namespace InvUserlikechat\Resources\snippet\fr_FR;

use Shopware\Core\System\Snippet\Files\SnippetFileInterface;

class SnippetFile_frFR implements SnippetFileInterface
{
    public function getName(): string
    {
        return 'storefront.fr-FR';
    }

    public function getPath(): string
    {
        return __DIR__ . '/storefront.fr-FR.json';
    }

    public function getIso(): string
    {
        return 'fr-FR';
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
