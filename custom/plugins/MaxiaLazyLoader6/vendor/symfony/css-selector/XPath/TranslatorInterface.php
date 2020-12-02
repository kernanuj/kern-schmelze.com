<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath;

use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\SelectorNode;
/**
 * XPath expression translator interface.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
interface TranslatorInterface
{
    /**
     * Translates a CSS selector to an XPath expression.
     */
    public function cssToXPath(string $cssExpr, string $prefix = 'descendant-or-self::') : string;
    /**
     * Translates a parsed selector node to an XPath expression.
     */
    public function selectorToXPath(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\SelectorNode $selector, string $prefix = 'descendant-or-self::') : string;
}
