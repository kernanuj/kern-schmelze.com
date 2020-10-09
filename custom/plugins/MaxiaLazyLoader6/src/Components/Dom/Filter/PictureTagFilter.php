<?php

declare (strict_types=1);
namespace Maxia\MaxiaLazyLoader6\Components\Dom\Filter;

use DOMDocument;
use DOMElement;
use Maxia\MaxiaLazyLoader6\Components\Dom\DomHelper;
/**
 * @package Maxia\MaxiaLazyLoader6\Components\Dom\Filter
 */
class PictureTagFilter implements \Maxia\MaxiaLazyLoader6\Components\Dom\Filter\FilterInterface
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
        return \iterator_to_array($dom->getElementsByTagName('picture'));
    }
    /**
     * @param DOMDocument $dom
     * @param DOMElement $element
     * @return DOMElement
     */
    public function updateElement(\DOMDocument $dom, \DOMElement $element) : \DOMElement
    {
        $images = $element->getElementsByTagName('img');
        if (!$images || !$images->count()) {
            return $element;
        }
        if (\Maxia\MaxiaLazyLoader6\Components\Dom\DomHelper::hasClass($element, $this->options['lazyClass']) || !$images[0]->parentNode) {
            return $element;
        }
        if ($this->options['noscriptFallback']) {
            $this->createFallback($dom, $element);
        }
        $sources = $element->getElementsByTagName('source');
        $hasSizes = \false;
        $hasSrcSet = \false;
        foreach ($sources as $source) {
            if ($source->hasAttribute('data-src') || $source->hasAttribute('data-srcset')) {
                continue;
            }
            if ($source->getAttribute('src')) {
                $source->setAttribute('data-src', $source->getAttribute('src'));
                $source->removeAttribute('src');
            }
            if ($source->getAttribute('srcset')) {
                $source->setAttribute('data-srcset', $source->getAttribute('srcset'));
                $source->removeAttribute('srcset');
                $hasSrcSet = \true;
            }
            if ($source->getAttribute('sizes')) {
                $source->setAttribute('data-sizes', $source->getAttribute('sizes'));
                $hasSizes = \true;
            } else {
                $source->setAttribute('data-sizes', 'auto');
            }
        }
        if ($images[0]->getAttribute('src')) {
            $images[0]->setAttribute('data-src', $images[0]->getAttribute('src'));
            $images[0]->removeAttribute('src');
        }
        if ($images[0]->getAttribute('srcset')) {
            $images[0]->setAttribute('data-srcset', $images[0]->getAttribute('srcset'));
            $images[0]->removeAttribute('srcset');
        }
        if ($images[0]->getAttribute('sizes')) {
            $images[0]->setAttribute('data-sizes', $images[0]->getAttribute('sizes'));
        } else {
            if (!$hasSizes && $hasSrcSet) {
                $images[0]->setAttribute('data-sizes', 'auto');
            }
        }
        $images[0]->setAttribute('class', $this->options['lazyClass'] . ' ' . $element->getAttribute('class'));
        $images[0]->setAttribute('src', $this->options['placeholder']);
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
