<?php

namespace _PhpScoper833c86d6963f\Masterminds\HTML5\Tests;

use _PhpScoper833c86d6963f\Masterminds\HTML5;
use _PhpScoper833c86d6963f\PHPUnit\Framework\TestCase as BaseTestCase;
class TestCase extends \_PhpScoper833c86d6963f\PHPUnit\Framework\TestCase
{
    const DOC_OPEN = '<!DOCTYPE html><html><head><title>test</title></head><body>';
    const DOC_CLOSE = '</body></html>';
    public function testFoo()
    {
        // Placeholder. Why is PHPUnit emitting warnings about no tests?
    }
    public function getInstance(array $options = array())
    {
        return new \_PhpScoper833c86d6963f\Masterminds\HTML5($options);
    }
    protected function wrap($fragment)
    {
        return self::DOC_OPEN . $fragment . self::DOC_CLOSE;
    }
}
