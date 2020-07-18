<?php declare(strict_types=1);

namespace InvExportLabel\Service\TypeInstance\MixerProduct;

use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\SourceItemType\MixerProductSourceItem;
use InvMixerProduct\Helper\OrderLineItemEntityAccessor;
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


        $ingredients = [];

        $baseProductLineItem = null;
        foreach ($orderLineItemEntity->getChildren() as $childLineItem) {
            if (OrderLineItemEntityAccessor::isContainsMixBaseProduct($childLineItem)) {
                $baseProductLineItem = $childLineItem;
            }
        }

        $baseProduct = $baseProductLineItem->getProduct();


        return (new MixerProductSourceItem())
            ->setMixName('Schokolade für meinen besten Freund mit den beste
Wünschen und alle, alles gute für die Zukunft :) :) :)')
            ->setIngredients('Zutaten: Dunkle Schokolade (80%) (Kakaomasse, Zucker,
Magerkakaopulver, Emulgator: Sojalecithin, natürliches
Vanillearoma), Nussmischung Australian Gold (20%)
(Haselnüsse blanchiert, Mandeln blanchiert, Cashews,
Pekannüsse, Macadamias, Erdnussöl)')
            ->setBestBeforeDate(
                $exportRequestConfiguration->getBestBeforeDate()
            );
    }
}
