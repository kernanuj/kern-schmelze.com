<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Struct;

use DateTimeInterface;
use Shopware\Core\Framework\Struct\Struct;

class Customer extends Struct
{
    /** @var null|DateTimeInterface */
    protected $birthday;

    /** @var null|string */
    protected $title = '';

    /** @var string */
    protected $type;

    /** @var null|string */
    protected $vatId;

    public function getBirthday(): ?DateTimeInterface
    {
        return $this->birthday;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getVatId(): ?string
    {
        return $this->vatId;
    }

    public function jsonSerialize(): array
    {
        $birthday = $this->getBirthday();

        return [
            'date_of_birth' => $birthday ? $birthday->format('Y-m-d') : null,
            'title'         => $this->getTitle(),
            'type'          => $this->getType(),
            'vat_id'        => $this->getVatId(),
        ];
    }
}
