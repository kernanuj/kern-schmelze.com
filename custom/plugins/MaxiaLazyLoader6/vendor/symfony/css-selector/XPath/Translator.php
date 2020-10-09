<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath;

use _PhpScoper833c86d6963f\Symfony\Component\CssSelector\Exception\ExpressionErrorException;
use _PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\FunctionNode;
use _PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\NodeInterface;
use _PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\SelectorNode;
use _PhpScoper833c86d6963f\Symfony\Component\CssSelector\Parser\Parser;
use _PhpScoper833c86d6963f\Symfony\Component\CssSelector\Parser\ParserInterface;
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
class Translator implements \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\TranslatorInterface
{
    private $mainParser;
    /**
     * @var ParserInterface[]
     */
    private $shortcutParsers = [];
    /**
     * @var Extension\ExtensionInterface[]
     */
    private $extensions = [];
    private $nodeTranslators = [];
    private $combinationTranslators = [];
    private $functionTranslators = [];
    private $pseudoClassTranslators = [];
    private $attributeMatchingTranslators = [];
    public function __construct(\_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Parser\ParserInterface $parser = null)
    {
        $this->mainParser = $parser ?: new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Parser\Parser();
        $this->registerExtension(new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\Extension\NodeExtension())->registerExtension(new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\Extension\CombinationExtension())->registerExtension(new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\Extension\FunctionExtension())->registerExtension(new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\Extension\PseudoClassExtension())->registerExtension(new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\Extension\AttributeMatchingExtension());
    }
    public static function getXpathLiteral(string $element) : string
    {
        if (\false === \strpos($element, "'")) {
            return "'" . $element . "'";
        }
        if (\false === \strpos($element, '"')) {
            return '"' . $element . '"';
        }
        $string = $element;
        $parts = [];
        while (\true) {
            if (\false !== ($pos = \strpos($string, "'"))) {
                $parts[] = \sprintf("'%s'", \substr($string, 0, $pos));
                $parts[] = "\"'\"";
                $string = \substr($string, $pos + 1);
            } else {
                $parts[] = "'{$string}'";
                break;
            }
        }
        return \sprintf('concat(%s)', \implode(', ', $parts));
    }
    /**
     * {@inheritdoc}
     */
    public function cssToXPath(string $cssExpr, string $prefix = 'descendant-or-self::') : string
    {
        $selectors = $this->parseSelectors($cssExpr);
        /** @var SelectorNode $selector */
        foreach ($selectors as $index => $selector) {
            if (null !== $selector->getPseudoElement()) {
                throw new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Exception\ExpressionErrorException('Pseudo-elements are not supported.');
            }
            $selectors[$index] = $this->selectorToXPath($selector, $prefix);
        }
        return \implode(' | ', $selectors);
    }
    /**
     * {@inheritdoc}
     */
    public function selectorToXPath(\_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\SelectorNode $selector, string $prefix = 'descendant-or-self::') : string
    {
        return ($prefix ?: '') . $this->nodeToXPath($selector);
    }
    /**
     * @return $this
     */
    public function registerExtension(\_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\Extension\ExtensionInterface $extension) : self
    {
        $this->extensions[$extension->getName()] = $extension;
        $this->nodeTranslators = \array_merge($this->nodeTranslators, $extension->getNodeTranslators());
        $this->combinationTranslators = \array_merge($this->combinationTranslators, $extension->getCombinationTranslators());
        $this->functionTranslators = \array_merge($this->functionTranslators, $extension->getFunctionTranslators());
        $this->pseudoClassTranslators = \array_merge($this->pseudoClassTranslators, $extension->getPseudoClassTranslators());
        $this->attributeMatchingTranslators = \array_merge($this->attributeMatchingTranslators, $extension->getAttributeMatchingTranslators());
        return $this;
    }
    /**
     * @throws ExpressionErrorException
     */
    public function getExtension(string $name) : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\Extension\ExtensionInterface
    {
        if (!isset($this->extensions[$name])) {
            throw new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Exception\ExpressionErrorException(\sprintf('Extension "%s" not registered.', $name));
        }
        return $this->extensions[$name];
    }
    /**
     * @return $this
     */
    public function registerParserShortcut(\_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Parser\ParserInterface $shortcut) : self
    {
        $this->shortcutParsers[] = $shortcut;
        return $this;
    }
    /**
     * @throws ExpressionErrorException
     */
    public function nodeToXPath(\_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\NodeInterface $node) : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        if (!isset($this->nodeTranslators[$node->getNodeName()])) {
            throw new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Exception\ExpressionErrorException(\sprintf('Node "%s" not supported.', $node->getNodeName()));
        }
        return $this->nodeTranslators[$node->getNodeName()]($node, $this);
    }
    /**
     * @throws ExpressionErrorException
     */
    public function addCombination(string $combiner, \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\NodeInterface $xpath, \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\NodeInterface $combinedXpath) : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        if (!isset($this->combinationTranslators[$combiner])) {
            throw new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Exception\ExpressionErrorException(\sprintf('Combiner "%s" not supported.', $combiner));
        }
        return $this->combinationTranslators[$combiner]($this->nodeToXPath($xpath), $this->nodeToXPath($combinedXpath));
    }
    /**
     * @throws ExpressionErrorException
     */
    public function addFunction(\_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr $xpath, \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\FunctionNode $function) : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        if (!isset($this->functionTranslators[$function->getName()])) {
            throw new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Exception\ExpressionErrorException(\sprintf('Function "%s" not supported.', $function->getName()));
        }
        return $this->functionTranslators[$function->getName()]($xpath, $function);
    }
    /**
     * @throws ExpressionErrorException
     */
    public function addPseudoClass(\_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr $xpath, string $pseudoClass) : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        if (!isset($this->pseudoClassTranslators[$pseudoClass])) {
            throw new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Exception\ExpressionErrorException(\sprintf('Pseudo-class "%s" not supported.', $pseudoClass));
        }
        return $this->pseudoClassTranslators[$pseudoClass]($xpath);
    }
    /**
     * @throws ExpressionErrorException
     */
    public function addAttributeMatching(\_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr $xpath, string $operator, string $attribute, $value) : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        if (!isset($this->attributeMatchingTranslators[$operator])) {
            throw new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Exception\ExpressionErrorException(\sprintf('Attribute matcher operator "%s" not supported.', $operator));
        }
        return $this->attributeMatchingTranslators[$operator]($xpath, $attribute, $value);
    }
    /**
     * @return SelectorNode[]
     */
    private function parseSelectors(string $css) : array
    {
        foreach ($this->shortcutParsers as $shortcut) {
            $tokens = $shortcut->parse($css);
            if (!empty($tokens)) {
                return $tokens;
            }
        }
        return $this->mainParser->parse($css);
    }
}
