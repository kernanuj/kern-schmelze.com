<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Helper\LocaleHelper;

interface LocaleHelperInterface
{
    public function getKlarnaLocale(string $locale, string $countryIso): string;
}
