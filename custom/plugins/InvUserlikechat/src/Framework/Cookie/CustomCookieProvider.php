<?php declare(strict_types=1);

namespace InvUserlikechat\Framework\Cookie;

use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class CustomCookieProvider implements CookieProviderInterface {

    private $originalService;

    function __construct( CookieProviderInterface $service ) {
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
        'snippet_name'        => 'cookie.groupFunctional.name',
        'snippet_description' => 'cookie.groupFunctional.description',
        'entries'             => [
            'snippet_name' => 'cookie.titles.userlike',
            'cookie'       => 'userlike-enabled',
            'value'        => '1',
            'expiration'   => '30'
        ]
    ];

    public function getCookieGroups(): array {
        $cookies              = $this->originalService->getCookieGroups();
        $functionalGroupExists = 0;

        foreach ( $cookies as &$cookie )
        {
            if ( $this->isFunctionalCookieGroup( $cookie ) )
            {
                $functionalGroupExists = 1;
            }
        }

        if ( $functionalGroupExists == 0 )
        {
            $cookies = array_merge(
                $this->originalService->getCookieGroups(),
                [
                    self::cookieGroup
                ]
            );
        }

        foreach ( $cookies as &$cookie )
        {
            if ( ! \is_array( $cookie ) )
            {
                continue;
            }

            if ( ! $this->isFunctionalCookieGroup( $cookie ) )
            {
                continue;
            }

            if ( ! \array_key_exists( 'entries', $cookie ) )
            {
                continue;
            }

            $cookie['entries'][] = [
                'snippet_name' => 'cookie.titles.userlike',
                'cookie'       => 'userlike-enabled',
                'value'        => '1',
                'expiration'   => '30'
            ];
        }

        return $cookies;
    }

    private function isFunctionalCookieGroup( array $cookie ): bool {
        return ( \array_key_exists( 'snippet_name', $cookie ) && $cookie['snippet_name'] === 'cookie.groupFunctional.name' );
    }
}
