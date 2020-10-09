<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node;

/**
 * Represents a "<selector>(::|:)<pseudoElement>" node.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class SelectorNode extends \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\AbstractNode
{
    private $tree;
    private $pseudoElement;
    public function __construct(\_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\NodeInterface $tree, string $pseudoElement = null)
    {
        $this->tree = $tree;
        $this->pseudoElement = $pseudoElement ? \strtolower($pseudoElement) : null;
    }
    public function getTree() : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\NodeInterface
    {
        return $this->tree;
    }
    public function getPseudoElement() : ?string
    {
        return $this->pseudoElement;
    }
    /**
     * {@inheritdoc}
     */
    public function getSpecificity() : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\Specificity
    {
        return $this->tree->getSpecificity()->plus(new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\Specificity(0, 0, $this->pseudoElement ? 1 : 0));
    }
    /**
     * {@inheritdoc}
     */
    public function __toString() : string
    {
        return \sprintf('%s[%s%s]', $this->getNodeName(), $this->tree, $this->pseudoElement ? '::' . $this->pseudoElement : '');
    }
}
