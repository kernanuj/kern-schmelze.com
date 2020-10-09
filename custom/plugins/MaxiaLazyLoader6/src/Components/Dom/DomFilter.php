<?php

declare (strict_types=1);
namespace Maxia\MaxiaLazyLoader6\Components\Dom;

use DOMDocument;
use DOMElement;
use _PhpScoper833c86d6963f\Masterminds\HTML5;
use Maxia\MaxiaLazyLoader6\Components\Dom\Filter\FilterInterface;
use Psr\Container\ContainerInterface;
use _PhpScoper833c86d6963f\Symfony\Component\CssSelector\CssSelectorConverter;
/**
 * @package MaxiaLazyLoader\Components
 */
class DomFilter
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var DOMDocument
     */
    protected $dom;
    /**
     * @var HTML5
     */
    protected $html5;
    /**
     * @var array
     */
    protected $errors;
    /**
     * @var bool
     */
    protected $isPartial = \false;
    /**
     * @var array
     */
    protected $blacklist = [];
    /**
     * @var array
     */
    protected $filters = [];
    /**
     * @var array
     */
    protected $blacklistElements = [];
    /**
     * LazyFilter constructor.
     */
    public function __construct(\Psr\Container\ContainerInterface $container)
    {
        $this->container = $container;
        $this->html5 = new \_PhpScoper833c86d6963f\Masterminds\HTML5();
    }
    /**
     * @param $selectors
     * @return DomFilter
     */
    public function setBlacklist($selectors)
    {
        $this->blacklist = $selectors;
        return $this;
    }
    /**
     * @return array
     */
    public function getBlacklist() : array
    {
        return $this->blacklist;
    }
    /**
     * @param array $filters
     * @return DomFilter
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
        return $this;
    }
    /**
     * @param FilterInterface $filter
     * @return DomFilter
     */
    public function addFilter(\Maxia\MaxiaLazyLoader6\Components\Dom\Filter\FilterInterface $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }
    public function getFilters() : array
    {
        return $this->filters;
    }
    /**
     * @return array
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
    /**
     * @param $html
     * @param bool $wrapImages
     * @return string
     */
    public function process($html, $wrapImages = \false)
    {
        // load DOM
        if (\strpos($html, '<!DOCTYPE') !== \false) {
            $this->dom = $this->html5->loadHTML($html);
            $this->isPartial = \false;
        } else {
            // add wrapper when loading partial html
            $elementId = 'lazy' . \uniqid();
            $doctype = '<!DOCTYPE html>';
            $prefix = '<div id="' . $elementId . '">';
            $suffix = '</div>';
            $this->isPartial = \true;
            $this->dom = $this->html5->loadHTML($doctype . $prefix . $html . $suffix);
        }
        $this->blacklistElements = [];
        foreach ($this->filters as $filter) {
            /** @var FilterInterface $filter */
            $nodes = \array_filter($filter->getElements($this->dom), function ($node) {
                return $node->parentNode && $node->parentNode->tagName != 'noscript' && !$this->isBlacklisted($node);
            });
            foreach ($nodes as &$node) {
                $filter->updateElement($this->dom, $node);
            }
        }
        // get final html
        if ($this->isPartial) {
            $result = $this->html5->saveHTML($this->dom->getElementById($elementId));
        } else {
            $result = $this->html5->saveHTML($this->dom);
        }
        $this->errors = $this->html5->getErrors();
        if ($this->isPartial) {
            if (\substr($result, 0, 5) == '<!DOC') {
                return \substr($result, \strlen($doctype) + \strlen($prefix), -\strlen($suffix));
            } else {
                return \substr($result, \strlen($prefix), -\strlen($suffix));
            }
        } else {
            return $result;
        }
    }
    /**
     * Checks if the given node lies inside or is itself a blacklisted element.
     *
     * @param DOMElement $node
     * @param int $maxLevels
     * @return bool
     */
    protected function isBlacklisted(\DOMElement $node, $maxLevels = 20)
    {
        if (empty($this->blacklist)) {
            return \false;
        }
        // query all blacklisted elements
        if (empty($this->blacklistElements)) {
            $converter = new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\CssSelectorConverter();
            $xpath = new \DOMXPath($this->dom);
            $this->blacklistElements = [];
            foreach ($this->blacklist as $query) {
                $nodes = $xpath->query($converter->toXPath($query), $this->dom);
                foreach ($nodes as $node) {
                    if ($node->nodeType != \XML_ELEMENT_NODE) {
                        continue;
                    }
                    $this->blacklistElements[] = $node;
                }
            }
        }
        $i = 0;
        do {
            // traverse up until we hit a blacklisted element
            if ($node->nodeType == \XML_ELEMENT_NODE) {
                foreach ($this->blacklistElements as $element) {
                    /** DOMElement $element */
                    if ($element->isSameNode($node)) {
                        return \true;
                    }
                }
            }
            $i++;
            $node = $node->parentNode;
        } while ($node->parentNode && $i < $maxLevels);
        return \false;
    }
}
