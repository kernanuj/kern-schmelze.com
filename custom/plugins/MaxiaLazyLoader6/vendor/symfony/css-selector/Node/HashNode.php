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
 * Represents a "<selector>#<id>" node.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class HashNode extends \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\AbstractNode
{
    private $selector;
    private $id;
    public function __construct(\_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\NodeInterface $selector, string $id)
    {
        $this->selector = $selector;
        $this->id = $id;
    }
    public function getSelector() : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\NodeInterface
    {
        return $this->selector;
    }
    public function getId() : string
    {
        return $this->id;
    }
    /**
     * {@inheritdoc}
     */
    public function getSpecificity() : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\Specificity
    {
        return $this->selector->getSpecificity()->plus(new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\Specificity(1, 0, 0));
    }
    /**
     * {@inheritdoc}
     */
    public function __toString() : string
    {
        return \sprintf('%s[%s#%s]', $this->getNodeName(), $this->selector, $this->id);
    }
}
