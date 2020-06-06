<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Struct;

use Shopware\Core\Framework\Struct\Struct;

class ExtraMerchantData extends Struct
{
    /** @var null|string */
    protected $merchantData;

    /** @var null|array */
    protected $attachment;

    public function getMerchantData(): ?string
    {
        if (empty($this->merchantData)) {
            return null;
        }

        return $this->merchantData;
    }

    public function getAttachment(): ?array
    {
        if (null === $this->attachment || empty($this->attachment)) {
            return null;
        }

        return $this->attachment;
    }
}
