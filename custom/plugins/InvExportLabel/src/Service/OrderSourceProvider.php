<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\SourceCollection;
use InvExportLabel\Value\SourceItemType\MixerProductSourceItem;

/**
 * Class OrderSourceProvider
 * @package InvExportLabel\Service
 */
class  OrderSourceProvider implements SourceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function fetchSourceCollection(ExportRequestConfiguration $configuration): SourceCollection
    {
        $collection = new SourceCollection();
        $collection->addItem(
            (new MixerProductSourceItem())
                ->setMixName('Schokolade für meinen besten Freund mit den beste
Wünschen und alle, alles gute für die Zukunft :) :) :)')
                ->setIngredients('Zutaten: Dunkle Schokolade (80%) (Kakaomasse, Zucker,
Magerkakaopulver, Emulgator: Sojalecithin, natürliches
Vanillearoma), Nussmischung Australian Gold (20%)
(Haselnüsse blanchiert, Mandeln blanchiert, Cashews,
Pekannüsse, Macadamias, Erdnussöl)')
                ->setBestBeforeDate(
                    $configuration->getBestBeforeDate()
                )

        );

        $collection->addItem(
            (new MixerProductSourceItem())
                ->setMixName(uniqid())
                ->setBestBeforeDate(
                    $configuration->getBestBeforeDate()
                )
        );

        return $collection;
    }
}
