<?php declare(strict_types=1);

namespace InvExportLabel\Service\TypeInstance\MixerProduct;

use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\SourceItemType\MixerProductSourceItem;
use InvMixerProduct\Helper\OrderLineItemEntityAccessor;
use InvMixerProduct\Helper\ProductEntityAccessor;
use InvMixerProduct\Value\Weight;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;

/**
 * Class SourceItemConverter
 * @package InvExportLabel\Service\TypeInstance\MixerProduct
 */
class SourceItemConverter
{

    /**
     * @param OrderLineItemEntity $orderLineItemEntity
     * @param ExportRequestConfiguration $exportRequestConfiguration
     * @return MixerProductSourceItem
     */
    public function convertOrderLineItem(
        OrderLineItemEntity $orderLineItemEntity,
        ExportRequestConfiguration $exportRequestConfiguration
    ): MixerProductSourceItem {

        return (new MixerProductSourceItem())
            ->setMixName(
                OrderLineItemEntityAccessor::getMixLabel($orderLineItemEntity) ?? 'Meine Schokolade'
            )
            ->setDisplayId(
                OrderLineItemEntityAccessor::getMixDisplayId($orderLineItemEntity)
            )
            ->setWeight(
                $this->extractWeight($orderLineItemEntity)
            )
            ->setIngredients(
                $this->extractIngredients($orderLineItemEntity)
            )
            ->setBestBeforeDate(
                $exportRequestConfiguration->getBestBeforeDate()
            )
            ->setOrderNumber(
                $orderLineItemEntity->getOrder()->getOrderNumber()
            );
    }

    /**
     * @param OrderLineItemEntity $orderLineItemEntity
     * @return Weight|null
     */
    private function extractWeight(OrderLineItemEntity $orderLineItemEntity)
    {
        $weight = Weight::aZeroGrams();
        foreach ($orderLineItemEntity->getChildren() as $child) {
            if (OrderLineItemEntityAccessor::isContainsMixBaseProduct($child)) {
                $weight = ProductEntityAccessor::getWeight(
                    $child->getProduct()
                );
            }
        }
        return $weight;
    }

    /**
     * @param OrderLineItemEntity $orderLineItemEntity
     * @return false|string[]
     */
    private function extractIngredients(OrderLineItemEntity $orderLineItemEntity)
    {
        $ingredients = [];

        foreach ($orderLineItemEntity->getChildren() as $childLineItem) {
            if (
                OrderLineItemEntityAccessor::isContainsMixBaseProduct($childLineItem)
                || OrderLineItemEntityAccessor::isContainsMixChildProduct($childLineItem)
            ) {
                $baseProduct = $childLineItem->getProduct();
                $ingredients[] = ProductEntityAccessor::fromCustomFieldsGetDataIngredients($baseProduct);
            }
        }

        $ingredients = explode(',', join(',', $ingredients));

        array_walk($ingredients, function (&$item) {
            $item = trim($item);
        });

        $ingredients = array_filter(
            $ingredients,
            function ($item) {
                return !empty($item);
            }
        );
        $ingredients = array_unique($ingredients);

        return $ingredients;
    }
}
