<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\CookieProvider;

use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class CookieProvider implements CookieProviderInterface
{
    private const requiredCookies = [
        [
            'snippet_name' => 'KlarnaPayment.cookie.klarna',
            'cookie'       => 'klarna',
        ],
        [
            'snippet_name' => 'KlarnaPayment.cookie.metrix',
            'cookie'       => 'thx_',
        ],
    ];

    /** @var CookieProviderInterface */
    private $parentProvider;

    public function __construct(CookieProviderInterface $parentProvider)
    {
        $this->parentProvider = $parentProvider;
    }

    public function getCookieGroups(): array
    {
        $groups = $this->parentProvider->getCookieGroups();

        $groups[0]['entries'] = array_merge($groups[0]['entries'], self::requiredCookies);

        return $groups;
    }
}
