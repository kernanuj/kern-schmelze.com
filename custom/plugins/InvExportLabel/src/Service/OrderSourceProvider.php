<?php declare(strict_types=1);

namespace InvExportLabel\Service;

use InvExportLabel\Repository\OrderRepository;
use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\SourceCollection;
use InvExportLabel\Value\SourceItemType\MixerProductSourceItem;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Framework\Context;

/**
 * Class OrderSourceProvider
 * @package InvExportLabel\Service
 */
class  OrderSourceProvider implements SourceProviderInterface
{

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var TypeInstanceRegistry
     */
    private $typeInstanceRegistry;

    /**
     * OrderSourceProvider constructor.
     * @param OrderRepository $orderRepository
     * @param TypeInstanceRegistry $typeInstanceRegistry
     */
    public function __construct(OrderRepository $orderRepository, TypeInstanceRegistry $typeInstanceRegistry)
    {
        $this->orderRepository = $orderRepository;
        $this->typeInstanceRegistry = $typeInstanceRegistry;
    }

    /**
     * @inheritDoc
     */
    public function fetchSourceCollection(ExportRequestConfiguration $configuration): SourceCollection
    {

        $typeInstance = $this->typeInstanceRegistry->forType($configuration->getType());

        $orderEntityCollection = $this->loadMatchingOrders(
            $configuration,
            Context::createDefaultContext()
        );

        $typeInstance->extractOrderLineItems($orderEntityCollection);


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

    /**
     * @param ExportRequestConfiguration $configuration
     * @param Context $context
     * @return OrderCollection
     */
    private function loadMatchingOrders(ExportRequestConfiguration $configuration, Context $context): OrderCollection
    {

        return $this->orderRepository->getOrdersForDateRange(
            $configuration->getSourceFilterDefinition()->getOrderedAtFrom(),
            $configuration->getSourceFilterDefinition()->getOrderedAtTo(),
            $context
        );

    }
}
