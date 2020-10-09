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
 * Represents a "<namespace>|<element>" node.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class ElementNode extends \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\AbstractNode
{
    private $namespace;
    private $element;
    public function __construct(string $namespace = null, string $element = null)
    {
        $this->namespace = $namespace;
        $this->element = $element;
    }
    public function getNamespace() : ?string
    {
        return $this->namespace;
    }
    public function getElement() : ?string
    {
        return $this->element;
    }
    /**
     * {@inheritdoc}
     */
    public function getSpecificity() : \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\Specificity
    {
        return new \_PhpScoper833c86d6963f\Symfony\Component\CssSelector\Node\Specificity(0, 0, $this->element ? 1 : 0);
    }
    /**
     * {@inheritdoc}
     */
    public function __toString() : string
    {
        $element = $this->element ?: '*';
        return \sprintf('%s[%s]', $this->getNodeName(), $this->namespace ? $this->namespace . '|' . $element : $element);
    }
}
