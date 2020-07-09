<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Validator;

use KlarnaPayment\Components\ConfigReader\ConfigReaderInterface;
use KlarnaPayment\Installer\PaymentMethodInstaller;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartValidatorInterface;
use Shopware\Core\Checkout\Cart\Error\ErrorCollection;
use Shopware\Core\Checkout\Payment\Cart\Error\PaymentMethodBlockedError;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentMethodValidator implements CartValidatorInterface
{
    /** @var \Shopware\Core\Checkout\Payment\Cart\PaymentMethodValidator */
    private $coreService;

    /** @var ConfigReaderInterface */
    private $configReader;

    public function __construct(
        \Shopware\Core\Checkout\Payment\Cart\PaymentMethodValidator $coreService,
        ConfigReaderInterface $configReader
    ) {
        $this->coreService  = $coreService;
        $this->configReader = $configReader;
    }

    public function validate(Cart $cart, ErrorCollection $errors, SalesChannelContext $context): void
    {
        if ($context->getPaymentMethod()->getId() === PaymentMethodInstaller::KLARNA_INSTANT_SHOPPING) {
            $pluginConfig = $this->configReader->read($context->getSalesChannel()->getId());

            if (!(bool) $pluginConfig->get('instantShoppingEnabled')) {
                $errors->add(
                    new PaymentMethodBlockedError((string) $context->getPaymentMethod()->getTranslation('name'))
                );
            }

            return;
        }

        $this->coreService->validate($cart, $errors, $context);
    }
}
