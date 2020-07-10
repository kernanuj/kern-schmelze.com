<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Struct;

use Shopware\Core\Framework\Struct\Struct;

class Styling extends Struct
{
    /** @var string */
    protected $variation;

    /** @var string */
    protected $type;

    public function getVariation(): string
    {
        return $this->variation;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function jsonSerialize(): array
    {
        return [
            'theme' => [
                'variation' => $this->getVariation(),
                'type'      => $this->getType(),
            ],
        ];
    }
}
