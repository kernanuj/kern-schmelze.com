<?php declare(strict_types=1);
/*

 */

namespace shopueberDeutschDu\Resources\app\core\snippet;

use Shopware\Core\System\Snippet\Files\SnippetFileInterface;
use shopueberDeutschDu\shopueberDeutschDu;

class LanguagePack implements SnippetFileInterface
{
    public function getName(): string
    {
        return 'shopueber.'. shopueberDeutschDu::SHOPUEBER_LOCALE_CODE;
    }

    public function getPath(): string
    {
        return __DIR__ . '/' . $this->getName() . '.json';
    }

    public function getIso(): string
    {
        return shopueberDeutschDu::SHOPUEBER_LOCALE_CODE;
    }

    public function getAuthor(): string
    {
        return 'Shop-Uebersetzungen.de';
    }

    public function isBase(): bool
    {
        return true;
    }
}
