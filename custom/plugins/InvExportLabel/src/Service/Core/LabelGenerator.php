<?php declare(strict_types=0);

namespace InvExportLabel\Service\Core;

use InvExportLabel\Repository\OrderRepository;
use InvExportLabel\Service\ConfigurationProvider;
use InvExportLabel\Service\TypeInstanceRegistry;
use InvExportLabel\Value\ExportRequestConfiguration;
use InvExportLabel\Value\SourceCollection;
use Shopware\Core\Checkout\Document\DocumentConfiguration;
use Shopware\Core\Checkout\Document\DocumentConfigurationFactory;
use Shopware\Core\Checkout\Document\DocumentGenerator\DocumentGeneratorInterface;
use Shopware\Core\Checkout\Document\Twig\DocumentTemplateRenderer;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class LabelGenerator implements DocumentGeneratorInterface
{
    public const INV_LABEL = 'inv_label';

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var DocumentTemplateRenderer
     */
    private $documentTemplateRenderer;

    /**
     * @var TypeInstanceRegistry
     */
    private $labelTypeInstanceRegistry;

    /**
     * @var ConfigurationProvider
     */
    private $labelConfigurationProvider;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * LabelGenerator constructor.
     * @param string $rootDir
     * @param DocumentTemplateRenderer $documentTemplateRenderer
     * @param TypeInstanceRegistry $labelTypeInstanceRegistry
     * @param ConfigurationProvider $labelConfigurationProvider
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        string $rootDir,
        DocumentTemplateRenderer $documentTemplateRenderer,
        TypeInstanceRegistry $labelTypeInstanceRegistry,
        ConfigurationProvider $labelConfigurationProvider,
        OrderRepository $orderRepository
    ) {
        $this->rootDir = $rootDir;
        $this->documentTemplateRenderer = $documentTemplateRenderer;
        $this->labelTypeInstanceRegistry = $labelTypeInstanceRegistry;
        $this->labelConfigurationProvider = $labelConfigurationProvider;
        $this->orderRepository = $orderRepository;
    }


    public function supports(): string
    {
        return self::INV_LABEL;
    }

    /**
     * @param OrderEntity $order
     * @param DocumentConfiguration $config
     * @param Context $context
     * @param string|null $templatePath
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Exception
     */
    public function generate(
        OrderEntity $order,
        DocumentConfiguration $config,
        Context $context,
        ?string $templatePath = null
    ): string {

        $fullOrder = $this->loadOrder($order, $context);
        $exportRequestConfiguration = $this->labelConfigurationProvider->buildConfigurationForSingleOrderInBackend($order);

        $sourceCollection = $this->getSourceCollection(
            $exportRequestConfiguration,
            $fullOrder
        );

        $documentConfig = DocumentConfigurationFactory::mergeConfiguration(
            $config,
            new DocumentConfiguration());
        $documentConfig->setDocumentNumber(
            (string)$order->getOrderNumber()
        );

        return $this->documentTemplateRenderer->render(
            '@InvExportLabel/documents/mixer_product_label.html.twig',
            [
                'order' => $order,
                'config' => $documentConfig->jsonSerialize(),
                'rootDir' => $this->rootDir,
                'context' => $context,
                'sourceCollection' => $sourceCollection
            ],
            $context,
            $order->getSalesChannelId(),
            $order->getLanguageId(),
            $order->getLanguage()->getLocale()->getCode()
        );
    }

    /**
     * @param OrderEntity $orderEntity
     * @param Context $context
     * @return OrderEntity
     */
    private function loadOrder(OrderEntity $orderEntity, Context $context): OrderEntity
    {
        return $this->orderRepository->getOrders([$orderEntity->getId()], $context)->first();
    }

    /**
     * @param ExportRequestConfiguration $exportRequestConfiguration
     * @param OrderEntity $orderEntity
     * @return SourceCollection
     */
    private function getSourceCollection(
        ExportRequestConfiguration $exportRequestConfiguration,
        OrderEntity $orderEntity
    ) {
        $collection = new SourceCollection();

        foreach ($exportRequestConfiguration->getSelectedTypes() as $labelType) {
            $typeInstance = $this->labelTypeInstanceRegistry->forType($labelType);

            $orderEntityCollection = new OrderCollection([$orderEntity]);

            $matchingOrderLineItems = $typeInstance->extractOrderLineItems($orderEntityCollection);

            foreach ($matchingOrderLineItems as $matchingOrderLineItem) {
                $converted = $typeInstance->convertOrderLineItemToSourceItem(
                    $matchingOrderLineItem,
                    $exportRequestConfiguration
                );
                for ($i = 0; $i < $matchingOrderLineItem->getQuantity(); $i++) {
                    $collection->addItem(
                        $converted
                    );
                }
            }
        }
        return $collection;
    }

    /**
     * @param DocumentConfiguration $config
     * @return string
     */
    public function getFileName(DocumentConfiguration $config): string
    {
        try {
            $documentNumber = $config->getDocumentNumber();
        } catch (\Throwable $e) {
            $documentNumber = 'xxx';
        }
        return $config->getFilenamePrefix() . $documentNumber . $config->getFilenameSuffix();
    }

}
