<?php

/**
 * @file
 * Test the Scanner. This requires the InputStream tests are all good.
 */
namespace _PhpScoper833c86d6963f\Masterminds\HTML5\Tests\Parser;

use _PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference;
class CharacterReferenceTest extends \_PhpScoper833c86d6963f\Masterminds\HTML5\Tests\TestCase
{
    public function testLookupName()
    {
        $this->assertEquals('&', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupName('amp'));
        $this->assertEquals('<', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupName('lt'));
        $this->assertEquals('>', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupName('gt'));
        $this->assertEquals('"', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupName('quot'));
        $this->assertEquals('∌', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupName('NotReverseElement'));
        $this->assertNull(\_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupName('StinkyCheese'));
    }
    public function testLookupHex()
    {
        $this->assertEquals('<', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupHex('3c'));
        $this->assertEquals('<', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupHex('003c'));
        $this->assertEquals('&', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupHex('26'));
        $this->assertEquals('}', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupHex('7d'));
        $this->assertEquals('Σ', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupHex('3A3'));
        $this->assertEquals('Σ', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupHex('03A3'));
        $this->assertEquals('Σ', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupHex('3a3'));
        $this->assertEquals('Σ', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupHex('03a3'));
    }
    public function testLookupDecimal()
    {
        $this->assertEquals('&', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupDecimal(38));
        $this->assertEquals('&', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupDecimal('38'));
        $this->assertEquals('<', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupDecimal(60));
        $this->assertEquals('Σ', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupDecimal(931));
        $this->assertEquals('Σ', \_PhpScoper833c86d6963f\Masterminds\HTML5\Parser\CharacterReference::lookupDecimal('0931'));
    }
}
