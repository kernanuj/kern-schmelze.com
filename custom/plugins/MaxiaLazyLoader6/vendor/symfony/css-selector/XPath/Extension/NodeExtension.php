<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\Extension;

use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node;
use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\Translator;
use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\XPathExpr;
/**
 * XPath expression translator node extension.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class NodeExtension extends \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\Extension\AbstractExtension
{
    const ELEMENT_NAME_IN_LOWER_CASE = 1;
    const ATTRIBUTE_NAME_IN_LOWER_CASE = 2;
    const ATTRIBUTE_VALUE_IN_LOWER_CASE = 4;
    private $flags;
    public function __construct(int $flags = 0)
    {
        $this->flags = $flags;
    }
    /**
     * @return $this
     */
    public function setFlag(int $flag, bool $on) : self
    {
        if ($on && !$this->hasFlag($flag)) {
            $this->flags += $flag;
        }
        if (!$on && $this->hasFlag($flag)) {
            $this->flags -= $flag;
        }
        return $this;
    }
    public function hasFlag(int $flag) : bool
    {
        return (bool) ($this->flags & $flag);
    }
    /**
     * {@inheritdoc}
     */
    public function getNodeTranslators() : array
    {
        return ['Selector' => [$this, 'translateSelector'], 'CombinedSelector' => [$this, 'translateCombinedSelector'], 'Negation' => [$this, 'translateNegation'], 'Function' => [$this, 'translateFunction'], 'Pseudo' => [$this, 'translatePseudo'], 'Attribute' => [$this, 'translateAttribute'], 'Class' => [$this, 'translateClass'], 'Hash' => [$this, 'translateHash'], 'Element' => [$this, 'translateElement']];
    }
    public function translateSelector(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\SelectorNode $node, \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\Translator $translator) : \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        return $translator->nodeToXPath($node->getTree());
    }
    public function translateCombinedSelector(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\CombinedSelectorNode $node, \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\Translator $translator) : \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        return $translator->addCombination($node->getCombinator(), $node->getSelector(), $node->getSubSelector());
    }
    public function translateNegation(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\NegationNode $node, \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\Translator $translator) : \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        $xpath = $translator->nodeToXPath($node->getSelector());
        $subXpath = $translator->nodeToXPath($node->getSubSelector());
        $subXpath->addNameTest();
        if ($subXpath->getCondition()) {
            return $xpath->addCondition(\sprintf('not(%s)', $subXpath->getCondition()));
        }
        return $xpath->addCondition('0');
    }
    public function translateFunction(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\FunctionNode $node, \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\Translator $translator) : \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        $xpath = $translator->nodeToXPath($node->getSelector());
        return $translator->addFunction($xpath, $node);
    }
    public function translatePseudo(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\PseudoNode $node, \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\Translator $translator) : \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        $xpath = $translator->nodeToXPath($node->getSelector());
        return $translator->addPseudoClass($xpath, $node->getIdentifier());
    }
    public function translateAttribute(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\AttributeNode $node, \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\Translator $translator) : \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        $name = $node->getAttribute();
        $safe = $this->isSafeName($name);
        if ($this->hasFlag(self::ATTRIBUTE_NAME_IN_LOWER_CASE)) {
            $name = \strtolower($name);
        }
        if ($node->getNamespace()) {
            $name = \sprintf('%s:%s', $node->getNamespace(), $name);
            $safe = $safe && $this->isSafeName($node->getNamespace());
        }
        $attribute = $safe ? '@' . $name : \sprintf('attribute::*[name() = %s]', \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\Translator::getXpathLiteral($name));
        $value = $node->getValue();
        $xpath = $translator->nodeToXPath($node->getSelector());
        if ($this->hasFlag(self::ATTRIBUTE_VALUE_IN_LOWER_CASE)) {
            $value = \strtolower($value);
        }
        return $translator->addAttributeMatching($xpath, $node->getOperator(), $attribute, $value);
    }
    public function translateClass(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\ClassNode $node, \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\Translator $translator) : \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        $xpath = $translator->nodeToXPath($node->getSelector());
        return $translator->addAttributeMatching($xpath, '~=', '@class', $node->getName());
    }
    public function translateHash(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\HashNode $node, \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\Translator $translator) : \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        $xpath = $translator->nodeToXPath($node->getSelector());
        return $translator->addAttributeMatching($xpath, '=', '@id', $node->getId());
    }
    public function translateElement(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Node\ElementNode $node) : \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\XPathExpr
    {
        $element = $node->getElement();
        if ($this->hasFlag(self::ELEMENT_NAME_IN_LOWER_CASE)) {
            $element = \strtolower($element);
        }
        if ($element) {
            $safe = $this->isSafeName($element);
        } else {
            $element = '*';
            $safe = \true;
        }
        if ($node->getNamespace()) {
            $element = \sprintf('%s:%s', $node->getNamespace(), $element);
            $safe = $safe && $this->isSafeName($node->getNamespace());
        }
        $xpath = new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\XPath\XPathExpr('', $element);
        if (!$safe) {
            $xpath->addNameTest();
        }
        return $xpath;
    }
    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return 'node';
    }
    private function isSafeName(string $name) : bool
    {
        return 0 < \preg_match('~^[a-zA-Z_][a-zA-Z0-9_.-]*$~', $name);
    }
}
