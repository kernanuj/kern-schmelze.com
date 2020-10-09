<?php

declare (strict_types=1);
namespace Maxia\MaxiaLazyLoader6\Components\Dom\Filter;

use DOMDocument;
use DOMElement;
use Maxia\MaxiaLazyLoader6\Components\Dom\DomHelper;
/**
 * @package Maxia\LazyLoader6\Components\Dom\Filter
 */
class StyleAttributeFilter implements \Maxia\MaxiaLazyLoader6\Components\Dom\Filter\FilterInterface
{
    /**
     * @var array
     */
    protected $options = ['lazyClass' => 'maxia-lazy-image'];
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
        return \Maxia\MaxiaLazyLoader6\Components\Dom\DomHelper::querySelectorAll($dom, '[style*="background-image:"]');
    }
    /**
     * @param DOMDocument $dom
     * @param DOMElement $element
     * @return DOMElement
     */
    public function updateElement(\DOMDocument $dom, \DOMElement $element) : \DOMElement
    {
        $styles = $element->getAttribute('style');
        // extract background-image url
        \preg_match('/background-image:\\s*url\\(\\"?\'?([^\'\\"]+)\\"?\'?\\)/', $styles, $matches);
        if (!isset($matches[1])) {
            return $element;
        }
        // remove background-image from style tag
        $styles = \preg_replace('/(\\s*background-image:[^;]+;\\s*)/', '', $styles);
        $element->setAttribute('style', $styles);
        // add bgset attribute
        $element->setAttribute('data-bg', $matches[1]);
        // add lazy class
        $classes = $element->getAttribute('class');
        $classes = !empty($classes) ? $classes . ' ' . $this->options['lazyClass'] : $this->options['lazyClass'];
        $element->setAttribute('class', $classes);
        return $element;
    }
}
