<?php

namespace CoeFacebookPixelSw6\CoeUtil;

use Symfony\Component\HttpFoundation\ServerBag;

/**
 * Class CoeBaseUtil
 * @package CoeFacebookPixelSw6\CoeUtil
 * @author Jeffry Block <jeffry.block@codeenterprise.de>
 */
class CoeBaseUtil
{

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public static function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    /**
     * @param ServerBag $serverBag
     * @return bool
     * @author Jeffry Block <jeffry.block@codeenterprise.de>
     */
    public static function isAjaxRequest(ServerBag $serverBag) : bool{
        $requestdWith = $serverBag->get("HTTP_X_REQUESTED_WITH");
        if(!$requestdWith){
            return false;
        }
        return strtolower($requestdWith) === "xmlhttprequest";
    }


}