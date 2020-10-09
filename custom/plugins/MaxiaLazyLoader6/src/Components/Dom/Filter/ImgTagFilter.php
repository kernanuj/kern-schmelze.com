<?php

declare (strict_types=1);
namespace Maxia\MaxiaLazyLoader6\Components\Dom\Filter;

use DOMDocument;
use DOMElement;
use Maxia\MaxiaLazyLoader6\Components\Dom\DomHelper;
/**
 * @package Maxia\LazyLoader6\Components\Dom\Filter
 */
class ImgTagFilter implements \Maxia\MaxiaLazyLoader6\Components\Dom\Filter\FilterInterface
{
    /**
     * @var array
     */
    protected $options = ['lazyClass' => 'maxia-lazy-image', 'placeholder' => "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==", 'noscriptFallback' => \true];
    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = \array_merge($this->options, $options);
    }
    /**
     * @param DOMDocument $dom
     * @return array
     */
    public function getElements(\DOMDocument $dom) : array
    {
        $elements = \iterator_to_array($dom->getElementsByTagName('img'));
        return \array_filter($elements, function ($element) {
            if (\Maxia\MaxiaLazyLoader6\Components\Dom\DomHelper::hasClass($element, $this->options['lazyClass'])) {
                return \false;
            }
            if ($element->parentNode->tagName == 'picture' || $element->parentNode->tagName == 'source') {
                return \false;
            }
            return \true;
        });
    }
    /**
     * @param DOMDocument $dom
     * @param DOMElement $element
     * @return DOMElement
     */
    public function updateElement(\DOMDocument $dom, \DOMElement $element) : \DOMElement
    {
        if ($this->options['noscriptFallback']) {
            $this->createFallback($dom, $element);
        }
        $hasSrcSet = \false;
        $element->setAttribute('class', $this->options['lazyClass'] . ' ' . $element->getAttribute('class'));
        if ($element->getAttribute('src')) {
            $element->setAttribute('data-src', $element->getAttribute('src'));
            $element->removeAttribute('src');
        }
        if ($element->getAttribute('srcset')) {
            $hasSrcSet = \true;
            $element->setAttribute('data-srcset', $element->getAttribute('srcset'));
            $element->removeAttribute('srcset');
        }
        if ($element->getAttribute('sizes')) {
            $element->setAttribute('data-sizes', $element->getAttribute('sizes'));
        } else {
            if ($hasSrcSet) {
                $element->setAttribute('data-sizes', 'auto');
            }
        }
        $element->setAttribute('src', $this->options['placeholder']);
        return $element;
    }
    /**
     * @param DOMDocument $dom
     * @param $node
     */
    protected function createFallback(\DOMDocument $dom, $node)
    {
        $noscript = $dom->createElement('noscript');
        $noscript->appendChild($node->cloneNode(\true));
        $noscript = $dom->importNode($noscript, \true);
        $node->parentNode->insertBefore($noscript, $node);
    }
}
