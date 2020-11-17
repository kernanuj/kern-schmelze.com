<?php declare(strict_types=1);

namespace CoeFacebookPixelSw6\Framework\Cookie;

use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class CustomCookieProvider implements CookieProviderInterface {

    private $originalService;

    public function __construct(CookieProviderInterface $service)
    {
        $this->originalService = $service;
    }

    private const singleCookie = [
        'snippet_name' => 'coe.facebookPixel',
        'snippet_description' => 'coe.facebookPixelDescription',
        'cookie' => 'coeFacebookPixel',
        'value'=> '1',
        'expiration' => '30'
    ];

    public function getCookieGroups(): array
    {
        return array_merge(
            $this->originalService->getCookieGroups(),
            [
                self::singleCookie
            ]
        );
    }
}