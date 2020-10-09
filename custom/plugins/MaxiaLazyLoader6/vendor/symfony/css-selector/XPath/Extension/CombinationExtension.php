<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\Extension;

use _PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr;
/**
 * XPath expression translator combination extension.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class CombinationExtension extends \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\Extension\AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getCombinationTranslators() : array
    {
        return [' ' => [$this, 'translateDescendant'], '>' => [$this, 'translateChild'], '+' => [$this, 'translateDirectAdjacent'], '~' => [$this, 'translateIndirectAdjacent']];
    }
    public function translateDescendant(\_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr $xpath, \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr $combinedXpath) : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        return $xpath->join('/descendant-or-self::*/', $combinedXpath);
    }
    public function translateChild(\_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr $xpath, \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr $combinedXpath) : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        return $xpath->join('/', $combinedXpath);
    }
    public function translateDirectAdjacent(\_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr $xpath, \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr $combinedXpath) : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        return $xpath->join('/following-sibling::', $combinedXpath)->addNameTest()->addCondition('position() = 1');
    }
    public function translateIndirectAdjacent(\_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr $xpath, \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr $combinedXpath) : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        return $xpath->join('/following-sibling::', $combinedXpath);
    }
    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return 'combination';
    }
}
