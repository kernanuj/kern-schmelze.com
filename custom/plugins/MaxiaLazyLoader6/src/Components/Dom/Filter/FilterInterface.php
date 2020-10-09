<?php

declare (strict_types=1);
namespace Maxia\MaxiaLazyLoader6\Components\Dom\Filter;

use DOMDocument;
use DOMElement;
interface FilterInterface
{
    /**
     * @param DOMDocument $dom
     * @return array
     */
    public function getElements(\DOMDocument $dom) : array;
    /**
     * @param DOMDocument $dom
     * @param DOMElement $element
     * @return DOMElement
     */
    public function updateElement(\DOMDocument $dom, \DOMElement $element) : \DOMElement;
}
