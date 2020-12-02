<?php

declare (strict_types=1);
namespace _PhpScoperfd240ab1f7e6\voku\helper;

interface HtmlMinDomObserverInterface
{
    /**
     * Receive dom elements before the minification.
     *
     * @param SimpleHtmlDomInterface $element
     * @param HtmlMinInterface       $htmlMin
     *
     * @return void
     */
    public function domElementBeforeMinification(\_PhpScoperfd240ab1f7e6\voku\helper\SimpleHtmlDomInterface $element, \_PhpScoperfd240ab1f7e6\voku\helper\HtmlMinInterface $htmlMin);
    /**
     * Receive dom elements after the minification.
     *
     * @param SimpleHtmlDomInterface $element
     * @param HtmlMinInterface       $htmlMin
     *
     * @return void
     */
    public function domElementAfterMinification(\_PhpScoperfd240ab1f7e6\voku\helper\SimpleHtmlDomInterface $element, \_PhpScoperfd240ab1f7e6\voku\helper\HtmlMinInterface $htmlMin);
}
