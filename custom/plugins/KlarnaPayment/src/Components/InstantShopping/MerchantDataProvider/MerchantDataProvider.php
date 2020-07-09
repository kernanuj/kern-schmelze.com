<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\MerchantDataProvider;

use KlarnaPayment\Components\Extension\SessionDataExtension;
use KlarnaPayment\Components\Factory\MerchantDataFactoryInterface;
use KlarnaPayment\Components\Struct\ExtraMerchantData;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class MerchantDataProvider implements MerchantDataProviderInterface
{
    /** @var MerchantDataFactoryInterface */
    private $merchantDataFactory;

    public function __construct(MerchantDataFactoryInterface $merchantDataFactory)
    {
        $this->merchantDataFactory = $merchantDataFactory;
    }

    public function getExtraMerchantData(
        SessionDataExtension $sessionData,
        Cart $cart,
        SalesChannelContext $context
    ): ExtraMerchantData {
        $data = $this->merchantDataFactory->getExtraMerchantData($sessionData, $cart, $context);

        $data->assign([
            'merchantData' => $this->buildCartIdentifierParams($sessionData),
        ]);

        return $data;
    }

    private function buildCartIdentifierParams(SessionDataExtension $sessionData): string
    {
        $cartIdentifier = [
            'klarna_cart_token' => $sessionData->getKlarnaCartToken(),
            'klarna_cart_hash'  => $sessionData->getKlarnaCartHash(),
        ];

        return (string) json_encode($cartIdentifier);
    }
}
