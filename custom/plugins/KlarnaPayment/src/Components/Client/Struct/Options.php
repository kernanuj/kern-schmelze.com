<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Struct;

use Shopware\Core\Framework\Struct\Struct;

class Options extends Struct
{
    /** @var array */
    protected $options = [];

    public function assign(array $options): self
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function jsonSerialize(): array
    {
        return $this->getOptions();
    }
}
