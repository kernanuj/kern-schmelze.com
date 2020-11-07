<?php declare(strict_types=1);

namespace InvExportLabel\Service\TypeInstance\SimpleProduct;

use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\SourceItemType\SimpleProductSourceItem;
use InvMixerProduct\Helper\ProductEntityAccessor;
use InvMixerProduct\Value\Weight;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;

/**
 * Class SourceItemConverter
 * @package InvExportLabel\Service\TypeInstance\SimpleProduct
 */
class SourceItemConverter
{

    /**
     * @param OrderLineItemEntity $orderLineItemEntity
     * @param ExportRequestConfiguration $exportRequestConfiguration
     * @return SimpleProductSourceItem
     */
    public function convertOrderLineItem(
        OrderLineItemEntity $orderLineItemEntity,
        ExportRequestConfiguration $exportRequestConfiguration
    ): SimpleProductSourceItem {

        return (new SimpleProductSourceItem())
            ->setProductName(
                $this->extractProductName($orderLineItemEntity)
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
        return ProductEntityAccessor::getWeight(
            $orderLineItemEntity->getProduct()
        );
    }

    /**
     * @param OrderLineItemEntity $orderLineItemEntity
     * @return string
     */
    private function extractProductName(OrderLineItemEntity $orderLineItemEntity):string
    {
        return $orderLineItemEntity->getLabel();
    }

    /**
     * @param OrderLineItemEntity $orderLineItemEntity
     * @return false|string[]
     */
    private function extractIngredients(OrderLineItemEntity $orderLineItemEntity)
    {
        $ingredients = [];

        $baseProduct = $orderLineItemEntity->getProduct();
        $ingredients[] = ProductEntityAccessor::fromCustomFieldsGetDataIngredients($baseProduct);

        array_walk($ingredients, function (&$item) {
            $item = trim($item);
        });

        $ingredients = array_filter(
            $ingredients,
            function ($item) {
                return !empty($item);
            }
        );
        //$ingredients = array_unique($ingredients);

        return $ingredients;
    }
}
