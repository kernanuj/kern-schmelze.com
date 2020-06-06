<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Struct;

use Shopware\Core\Framework\Struct\Struct;

class Attachment extends Struct
{
    /** @var array */
    protected $data = [];

    public function getData(): array
    {
        return $this->data;
    }

    public function jsonSerialize(): array
    {
        return [
            'content_type' => 'application/vnd.klarna.internal.emd-v2+json',
            'body'         => json_encode($this->getData(), JSON_PRESERVE_ZERO_FRACTION),
        ];
    }
}
