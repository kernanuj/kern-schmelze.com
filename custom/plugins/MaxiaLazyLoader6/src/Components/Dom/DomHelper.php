<?php

declare (strict_types=1);
namespace Maxia\MaxiaLazyLoader6\Components\Dom;

use DOMDocument;
use DOMElement;
use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\CssSelectorConverter;
class DomHelper
{
    /**
     * Checks if the element has a specific class.
     *
     * @param DOMElement $element
     * @param $class
     * @return bool
     */
    public static function hasClass(\DOMElement $element, $class)
    {
        return \strpos($element->getAttribute('class'), $class) !== \false;
    }
    /**
     * @param DOMDocument $dom
     * @param $selector
     * @param bool $iterator
     * @return \DOMNodeList|array
     */
    public static function querySelectorAll(\DOMDocument $dom, $selector, $iterator = \false)
    {
        $xpath = new \DOMXPath($dom);
        $converter = new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\CssSelectorConverter();
        $result = $xpath->query($converter->toXPath($selector), $dom);
        return $iterator ? $result : \iterator_to_array($result);
    }
    /**
     * Inserts a noscript tag before the given image
     *
     * @param DOMDocument $dom
     * @param $node
     */
    public static function createNoscriptFallback(\DOMDocument $dom, $node)
    {
        $noscript = $dom->createElement('noscript');
        $noscript->appendChild($node->cloneNode(\true));
        $noscript = $dom->importNode($noscript, \true);
        $node->parentNode->insertBefore($noscript, $node);
    }
}
