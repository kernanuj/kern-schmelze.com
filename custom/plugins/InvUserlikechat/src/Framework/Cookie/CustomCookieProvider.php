<?php declare(strict_types=1);

namespace InvUserlikechat\Framework\Cookie;

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
        'snippet_name' => 'InvUserlikechat.cookie.group.name',
        'snippet_description' => 'InvUserlikechat.cookie.group.description',
        'entries' => [
            [
                'snippet_name' => 'InvUserlikechat.cookie.titles.userlike',
                'cookie' => 'userlike-enabled',
                'value'=> '1',
                'expiration' => '365'
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
