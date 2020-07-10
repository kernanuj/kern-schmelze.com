<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Extension;

use Shopware\Core\Framework\Struct\Struct;

class GuestCustomerRegistrationExtension extends Struct
{
    public const EXTENSION_NAME = 'klarna_instant_shopping_customer_registration';
}
