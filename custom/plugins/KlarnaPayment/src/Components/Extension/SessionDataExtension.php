<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Extension;

use Shopware\Core\Framework\Struct\Struct;

class SessionDataExtension extends Struct
{
    public const EXTENSION_NAME = 'klarna_session_data';

    /** @var string */
    protected $sessionId = '';

    /** @var string */
    protected $clientToken = '';

    /** @var array */
    protected $paymentMethodCategories = [];

    /** @var string */
    protected $selectedPaymentMethodCategory;

    /** @var string */
    protected $cartHash = '';

    /** @var array */
    protected $customerData = [];

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function getClientToken(): string
    {
        return $this->clientToken;
    }

    public function getPaymentMethodCategories(): array
    {
        return $this->paymentMethodCategories;
    }

    public function getSelectedPaymentMethodCategory(): string
    {
        return $this->selectedPaymentMethodCategory;
    }

    public function getCartHash(): string
    {
        return $this->cartHash;
    }

    public function getCustomerData(): array
    {
        return $this->customerData;
    }
}
