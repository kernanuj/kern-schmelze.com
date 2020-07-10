<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\DataAbstractionLayer\Entity\RequestLog;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class RequestLogEntity extends Entity
{
    use EntityIdTrait;

    /** @var string */
    protected $klarnaOrderId;

    /** @var string */
    protected $callType;

    /** @var null|array */
    protected $request;

    /** @var null|array */
    protected $response;

    /** @var string */
    protected $idempotencyKey;

    public function getKlarnaOrderId(): string
    {
        return $this->klarnaOrderId;
    }

    public function setKlarnaOrderId(string $klarnaOrderId): void
    {
        $this->klarnaOrderId = $klarnaOrderId;
    }

    public function getCallType(): string
    {
        return $this->callType;
    }

    public function setCallType(string $callType): void
    {
        $this->callType = $callType;
    }

    public function getRequest(): ?array
    {
        return $this->request;
    }

    public function setRequest(array $request): void
    {
        $this->request = $request;
    }

    public function getResponse(): ?array
    {
        return $this->response;
    }

    public function setResponse(array $response): void
    {
        $this->response = $response;
    }

    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey;
    }

    public function setIdempotencyKey(string $idempotencyKey): void
    {
        $this->idempotencyKey = $idempotencyKey;
    }
}
