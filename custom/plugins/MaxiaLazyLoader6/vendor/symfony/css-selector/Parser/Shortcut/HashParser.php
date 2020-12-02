<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Shortcut;

use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\ElementNode;
use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\HashNode;
use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\SelectorNode;
use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\ParserInterface;
/**
 * CSS selector hash parser shortcut.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class HashParser implements \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $source) : array
    {
        // Matches an optional namespace, optional element, and required id
        // $source = 'test|input#ab6bd_field';
        // $matches = array (size=4)
        //     0 => string 'test|input#ab6bd_field' (length=22)
        //     1 => string 'test' (length=4)
        //     2 => string 'input' (length=5)
        //     3 => string 'ab6bd_field' (length=11)
        if (\preg_match('/^(?:([a-z]++)\\|)?+([\\w-]++|\\*)?+#([\\w-]++)$/i', \trim($source), $matches)) {
            return [new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\SelectorNode(new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\HashNode(new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\ElementNode($matches[1] ?: null, $matches[2] ?: null), $matches[3]))];
        }
        return [];
    }
}
