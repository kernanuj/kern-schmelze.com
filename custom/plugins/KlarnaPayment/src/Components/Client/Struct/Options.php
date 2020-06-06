<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Struct;

use Shopware\Core\Framework\Struct\Struct;

class Options extends Struct
{
    /** @var bool */
    protected $disableConfirmationModals = true;

    public function getDisableConfirmationModals(): bool
    {
        return $this->disableConfirmationModals;
    }

    public function jsonSerialize(): array
    {
        return [
            'disable_confirmation_modals' => $this->getDisableConfirmationModals(),
        ];
    }
}
