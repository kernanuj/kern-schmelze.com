<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Tokenizer;

use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Handler;
use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Reader;
use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Token;
use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\TokenStream;
/**
 * CSS selector tokenizer.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
class Tokenizer
{
    /**
     * @var Handler\HandlerInterface[]
     */
    private $handlers;
    public function __construct()
    {
        $patterns = new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Tokenizer\TokenizerPatterns();
        $escaping = new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Tokenizer\TokenizerEscaping($patterns);
        $this->handlers = [new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Handler\WhitespaceHandler(), new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Handler\IdentifierHandler($patterns, $escaping), new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Handler\HashHandler($patterns, $escaping), new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Handler\StringHandler($patterns, $escaping), new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Handler\NumberHandler($patterns), new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Handler\CommentHandler()];
    }
    /**
     * Tokenize selector source code.
     */
    public function tokenize(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Reader $reader) : \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\TokenStream
    {
        $stream = new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\TokenStream();
        while (!$reader->isEOF()) {
            foreach ($this->handlers as $handler) {
                if ($handler->handle($reader, $stream)) {
                    continue 2;
                }
            }
            $stream->push(new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Token(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Token::TYPE_DELIMITER, $reader->getSubstring(1), $reader->getPosition()));
            $reader->moveForward(1);
        }
        return $stream->push(new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Token(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Token::TYPE_FILE_END, null, $reader->getPosition()))->freeze();
    }
}
