<?php declare(strict_types=1);

namespace InvMixerProduct\EntityDefinition;

use InvMixerProduct\DataAbstractionLayer\ContainerDefinitionField;
use InvMixerProduct\Entity\MixEntity as SubjectEntity;
use InvMixerProduct\Entity\MixEntityCollection as SubjectEntityCollection;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * Class MixEntityDefinition
 * @package InvMixerProduct\EntityDefinition
 */
class MixEntityDefinition extends EntityDefinition
{
    /**
     *
     */
    public const ENTITY_NAME = 'inv_mixer_product__mix';

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    /**
     * @return string
     */
    public function getCollectionClass(): string
    {
        return SubjectEntityCollection::class;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return SubjectEntity::class;
    }

    /**
     * @return FieldCollection
     */
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new IntField('display_id', 'displayId'))->addFlags(new Required()),
            (new FkField('customer_id', 'customerId', CustomerDefinition::class)),
            new CreatedAtField(),
            new DateField('updated_at', 'updatedAt'),
            new StringField('label', 'label'),
            (new ContainerDefinitionField('container_definition', 'containerDefinition'))->addFlags(new Required()),
            new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class, 'id', true),
            (new OneToManyAssociationField('items', MixItemEntityDefinition::class, 'mix_id', 'id'))->addFlags(new CascadeDelete()),
        ]);
    }
}
