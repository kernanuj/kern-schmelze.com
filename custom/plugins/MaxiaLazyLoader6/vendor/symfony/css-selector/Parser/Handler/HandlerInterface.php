<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Handler;

use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Reader;
use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\TokenStream;
/**
 * CSS selector handler interface.
 *
 * This component is a port of the Python cssselect library,
 * which is copyright Ian Bicking, @see https://github.com/SimonSapin/cssselect.
 *
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
interface HandlerInterface
{
    public function handle(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\Reader $reader, \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\Parser\TokenStream $stream) : bool;
}
