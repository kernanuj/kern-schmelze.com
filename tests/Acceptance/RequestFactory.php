<?php declare(strict_types=1);

namespace InvTest\Acceptance;

use InvTest\Library\Settings;

/**
 * Class Request
 * @package InvTest\Acceptance
 */
class RequestFactory
{

    /**
     * @return Request
     */
    public static function create(): Request
    {
        return (new Request())->setBaseUrl(Settings::BASE_URL);
    }

}
