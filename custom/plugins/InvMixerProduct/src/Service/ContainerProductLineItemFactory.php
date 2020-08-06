<?php declare(strict_types=1);

namespace InvMixerProduct\Service;

use InvMixerProduct\Constants;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryHandler\LineItemFactoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class ContainerProductLineItemFactory
 * @package InvMixerProduct\Service
 */
class ContainerProductLineItemFactory implements LineItemFactoryInterface
{

    /**
     * @inheritDoc
     */
    public function supports(string $type): bool
    {
        return $type === Constants::LINE_ITEM_TYPE_IDENTIFIER;
    }

    /**
     * @inheritDoc
     */
    public function create(array $data, SalesChannelContext $context): LineItem
    {
        $lineItem = new LineItem(
            $data['id'],
            Constants::LINE_ITEM_TYPE_IDENTIFIER,
            $data['referencedId'] ?? null,
            $data['quantity']
        );
        $lineItem->markModified();
        $lineItem->setRemovable(true);
        $lineItem->setStackable(true);

        return $lineItem;
    }

    /**
     * @inheritDoc
     */
    public function update(LineItem $lineItem, array $data, SalesChannelContext $context): void
    {
        if (isset($data['referencedId'])) {
            $lineItem->setReferencedId($data['referencedId']);
        }

        if (isset($data['quantity'])) {
            $lineItem->setQuantity((int)$data['quantity']);
        }
    }
}
