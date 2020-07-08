<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

if (!isset($productUuid)) {
    $productUuid = Uuid::randomHex();
}

return [
    'id' => $productUuid,
    'active' => true,
    'stock' => 150,
    'tax' => ['id' => Uuid::randomHex(), 'taxRate' => 19, 'name' => 'tax'],
    'price' => [
        [
            'currencyId' => Defaults::CURRENCY,
            'net' => 934.58,
            'gross' => 1000.0,
            'linked' => true,
        ],
    ],
    'productNumber' => 'CuPro-Test-8' . Uuid::randomHex(),
    'isCloseout' => false,
    'purchaseSteps' => 1,
    'minPurchase' => 1,
    'shippingFree' => false,
    'restockTime' => 3,
    'name' => 'Tattoo',
    'visibilities' => [
        [
            'salesChannelId' => Defaults::SALES_CHANNEL,
            'visibility' => 30,
        ],
    ],
    'swagCustomizedProductsTemplate' => [
        'internalName' => 'Tattoo',
        'displayName' => 'Deine Tattoo',
        'active' => true,
        'stepByStep' => false,
        'confirmInput' => false,
        'options' => [
            [
                'type' => 'select',
                'displayName' => 'Körperteil',
                'typeProperties' => [
                    'isMultiSelect' => true,
                    'isDropDown' => false,
                ],
                'required' => false,
                'oneTimeSurcharge' => true,
                'relativeSurcharge' => false,
                'advancedSurcharge' => true,
                'position' => 2,
                'price' => [
                    [
                        'currencyId' => Defaults::CURRENCY,
                        'net' => 0.0,
                        'gross' => 0.0,
                        'linked' => true,
                    ],
                ],
                'percentageSurcharge' => 0.0,
                'values' => [
                    [
                        'value' => [
                            '_value' => '',
                        ],
                        'displayName' => 'Kopf',
                        'default' => false,
                        'oneTimeSurcharge' => false,
                        'relativeSurcharge' => false,
                        'advancedSurcharge' => false,
                        'position' => 4,
                        'price' => [
                            [
                                'currencyId' => Defaults::CURRENCY,
                                'net' => 0.0,
                                'gross' => 0.0,
                                'linked' => true,
                            ],
                        ],
                        'percentageSurcharge' => 0.0,
                    ],
                    [
                        'value' => [
                            '_value' => '',
                        ],
                        'displayName' => 'Arm',
                        'default' => false,
                        'oneTimeSurcharge' => false,
                        'relativeSurcharge' => false,
                        'advancedSurcharge' => false,
                        'position' => 3,
                        'price' => [
                            [
                                'currencyId' => Defaults::CURRENCY,
                                'net' => 0.0,
                                'gross' => 0.0,
                                'linked' => true,
                            ],
                        ],
                        'percentageSurcharge' => 0.0,
                    ],
                    [
                        'value' => [
                            '_value' => '',
                        ],
                        'displayName' => 'Beine',
                        'default' => false,
                        'oneTimeSurcharge' => false,
                        'relativeSurcharge' => false,
                        'advancedSurcharge' => false,
                        'position' => 1,
                        'price' => [
                            [
                                'currencyId' => Defaults::CURRENCY,
                                'net' => 0.0,
                                'gross' => 0.0,
                                'linked' => true,
                            ],
                        ],
                        'percentageSurcharge' => 0.0,
                    ],
                ],
            ],
        ],
    ],
];
