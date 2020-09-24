<?php declare(strict_types=1);

namespace InvTrackingBelboon\Framework\Cookie;

use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class CustomCookieProvider implements CookieProviderInterface {

    private $originalService;

    function __construct(CookieProviderInterface $service)
    {
        $this->originalService = $service;
    }

    /*private const singleCookie = [
        'snippet_name' => 'cookie.name',
        'snippet_description' => 'cookie.description ',
        'cookie' => 'cookie-key',
        'value'=> 'cookie value',
        'expiration' => '30'
    ];*/

    // cookies can also be provided as a group
    private const cookieGroup = [
        'snippet_name' => 'InvTrackingBelboon.cookie.group.name',
        'snippet_description' => 'InvTrackingBelboon.cookie.group.description',
        'entries' => [
            [
                'snippet_name' => 'InvTrackingBelboon.cookie.titles.belboon',
                'cookie' => 'belboon-enabled',
                'value'=> '1',
                'expiration' => '30'
            ]/*,
            [
                'snippet_name' => 'cookie.second_child_name',
                'cookie' => 'cookie-key-2',
                'value'=> 'cookie value',
                'expiration' => '60'
            ]*/
        ],
    ];

    public function getCookieGroups(): array
    {
        return array_merge(
            $this->originalService->getCookieGroups(),
            [
                self::cookieGroup/*,
                self::singleCookie*/
            ]
        );
    }
}
