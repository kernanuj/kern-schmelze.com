<?php

declare (strict_types=1);
namespace _PhpScoperfd240ab1f7e6\voku\helper;

use _PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\CssSelectorConverter;
class SelectorConverter
{
    /**
     * @var array
     */
    protected static $compiled = [];
    /**
     * @param string $selector
     *
     * @throws \RuntimeException
     *
     * @return mixed|string
     */
    public static function toXPath(string $selector)
    {
        if (isset(self::$compiled[$selector])) {
            return self::$compiled[$selector];
        }
        // Select DOMText
        if ($selector === 'text') {
            return '//text()';
        }
        // Select DOMComment
        if ($selector === 'comment') {
            return '//comment()';
        }
        if (\strpos($selector, '//') === 0) {
            return $selector;
        }
        if (!\class_exists(\_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\CssSelectorConverter::class)) {
            throw new \RuntimeException('Unable to filter with a CSS selector as the Symfony CssSelector 2.8+ is not installed (you can use filterXPath instead).');
        }
        $converter = new \_PhpScoperfd240ab1f7e6\Symfony\Component\CssSelector\CssSelectorConverter(\true);
        $xPathQuery = $converter->toXPath($selector);
        self::$compiled[$selector] = $xPathQuery;
        return $xPathQuery;
    }
}
