<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\DataAbstractionLayer\Entity\ButtonKey;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainDefinition;

class ButtonKeyDefinition extends EntityDefinition
{
    public function getEntityName(): string
    {
        return 'klarna_payment_button_key';
    }

    public function getCollectionClass(): string
    {
        return ButtonKeyCollection::class;
    }

    public function getEntityClass(): string
    {
        return ButtonKeyEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->setFlags(new PrimaryKey(), new Required()),

            (new StringField('button_key', 'buttonKey'))->setFlags(new Required()),

            (new FkField('sales_channel_domain_id', 'salesChannelDomainId', SalesChannelDomainDefinition::class))->addFlags(new Required()),
            (new OneToOneAssociationField('salesChannelDomain', 'sales_channel_domain_id', 'id', SalesChannelDomainDefinition::class, false)),

            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}
