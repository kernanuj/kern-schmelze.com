<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\LocaleHelper;

class LocaleHelper implements LocaleHelperInterface
{
    // en is used as a fallback for all countries
    private const LOCALES = [
        'AT' => [
            'de',
        ],
        'DK' => [
            'da',
        ],
        'FI' => [
            'fi',
            'sv',
        ],
        'DE' => [
            'de',
        ],
        'NL' => [
            'nl',
        ],
        'NO' => [
            'nb',
        ],
        'SE' => [
            'sv',
        ],
        'CH' => [
            'de',
            'fr',
            'it',
        ],
    ];

    public function getKlarnaLocale(string $locale, string $countryIso): string
    {
        if (empty($countryIso)) {
            return $locale;
        }

        $splitLocale  = explode('-', $locale);
        $actualLocale = $splitLocale[0];

        if (!array_key_exists($countryIso, self::LOCALES) || !in_array($actualLocale, self::LOCALES[$countryIso])) {
            $actualLocale = 'en';
        }

        return $actualLocale . '-' . $countryIso;
    }
}
