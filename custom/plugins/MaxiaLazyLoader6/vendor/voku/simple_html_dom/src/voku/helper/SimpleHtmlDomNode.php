<?php

declare (strict_types=1);
namespace _PhpScoperfd240ab1f7e6\voku\helper;

/**
 * {@inheritdoc}
 */
class SimpleHtmlDomNode extends \_PhpScoperfd240ab1f7e6\voku\helper\AbstractSimpleHtmlDomNode implements \_PhpScoperfd240ab1f7e6\voku\helper\SimpleHtmlDomNodeInterface
{
    /**
     * Find list of nodes with a CSS selector.
     *
     * @param string   $selector
     * @param int|null $idx
     *
     * @return SimpleHtmlDomNodeInterface<SimpleHtmlDomInterface>|SimpleHtmlDomNodeInterface[]|null
     */
    public function find(string $selector, $idx = null)
    {
        // init
        $elements = new static();
        foreach ($this as $node) {
            \assert($node instanceof \_PhpScoperfd240ab1f7e6\voku\helper\SimpleHtmlDomInterface);
            foreach ($node->find($selector) as $res) {
                $elements[] = $res;
            }
        }
        // return all elements
        if ($idx === null) {
            if (\count($elements) === 0) {
                return new \_PhpScoperfd240ab1f7e6\voku\helper\SimpleHtmlDomNodeBlank();
            }
            return $elements;
        }
        // handle negative values
        if ($idx < 0) {
            $idx = \count($elements) + $idx;
        }
        // return one element
        return $elements[$idx] ?? null;
    }
    /**
     * Find nodes with a CSS selector.
     *
     * @param string $selector
     *
     * @return SimpleHtmlDomInterface[]|SimpleHtmlDomNodeInterface<SimpleHtmlDomInterface>
     */
    public function findMulti(string $selector) : \_PhpScoperfd240ab1f7e6\voku\helper\SimpleHtmlDomNodeInterface
    {
        return $this->find($selector, null);
    }
    /**
     * Find nodes with a CSS selector.
     *
     * @param string $selector
     *
     * @return false|SimpleHtmlDomInterface[]|SimpleHtmlDomNodeInterface<SimpleHtmlDomInterface>
     */
    public function findMultiOrFalse(string $selector)
    {
        $return = $this->find($selector, null);
        if ($return instanceof \_PhpScoperfd240ab1f7e6\voku\helper\SimpleHtmlDomNodeBlank) {
            return \false;
        }
        return $return;
    }
    /**
     * Find one node with a CSS selector.
     *
     * @param string $selector
     *
     * @return SimpleHtmlDomNodeInterface<SimpleHtmlDomInterface>|null
     */
    public function findOne(string $selector)
    {
        return $this->find($selector, 0);
    }
    /**
     * Find one node with a CSS selector.
     *
     * @param string $selector
     *
     * @return false|SimpleHtmlDomNodeInterface<SimpleHtmlDomInterface>
     */
    public function findOneOrFalse(string $selector)
    {
        $return = $this->find($selector, 0);
        if ($return === null) {
            return \false;
        }
        return $return;
    }
    /**
     * Get html of elements.
     *
     * @return string[]
     */
    public function innerHtml() : array
    {
        // init
        $html = [];
        foreach ($this as $node) {
            $html[] = $node->outertext;
        }
        return $html;
    }
    /**
     * alias for "$this->innerHtml()" (added for compatibly-reasons with v1.x)
     *
     * @return string[]
     */
    public function innertext()
    {
        return $this->innerHtml();
    }
    /**
     * alias for "$this->innerHtml()" (added for compatibly-reasons with v1.x)
     *
     * @return string[]
     */
    public function outertext()
    {
        return $this->innerHtml();
    }
    /**
     * Get plain text.
     *
     * @return string[]
     */
    public function text() : array
    {
        // init
        $text = [];
        foreach ($this as $node) {
            $text[] = $node->plaintext;
        }
        return $text;
    }
}
