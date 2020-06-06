<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\DataAbstractionLayer\Entity\Log;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class KlarnaPaymentLogDefinition extends EntityDefinition
{
    public function getEntityName(): string
    {
        return 'klarna_payment_log';
    }

    public function getCollectionClass(): string
    {
        return KlarnaPaymentLogCollection::class;
    }

    public function getEntityClass(): string
    {
        return KlarnaPaymentLogEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new PrimaryKey(), new Required()),

            (new StringField('klarna_order_id', 'klarnaOrderId'))->setFlags(new Required()),
            (new StringField('call_type', 'callType'))->setFlags(new Required()),
            (new JsonField('request', 'request'))->setFlags(new Required()),
            (new JsonField('response', 'response'))->setFlags(new Required()),
            (new StringField('idempotency_key', 'idempotencyKey'))->setFlags(new Required()),

            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}
