<?php declare(strict_types=1);

namespace InvMixerProduct\Test\Acceptance;

use InvMixerProduct\Test\Library\Settings;

/**
 * Class Request
 * @package InvMixerProduct\Test\Acceptance
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
