<?php declare(strict_types=1);

namespace InvProductImageDocuments\Service\Decorator;

use Shopware\Core\Checkout\Document\DocumentConfiguration;
use Shopware\Core\Checkout\Document\DocumentGenerator\DeliveryNoteGenerator;
use Shopware\Core\Checkout\Document\DocumentGenerator\DocumentGeneratorInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use InvProductImageDocuments\Service\Configuration;
use InvProductImageDocuments\Service\DocumentService;

/**
 * Class DeliveryNoteGeneratorDecorated
 *
 * @author    Nils Harder <hallo@inventivo.de>
 * @copyright Copyright (c) 2020 Nils Harder
 * @package   InvProductImageDocuments\Service\Decorator
 * @version   1
 */
class DeliveryNoteGeneratorDecorated implements DocumentGeneratorInterface
{
    /** @var DeliveryNoteGenerator */
    private $decoratedService;

    /**  @var Configuration */
    private $configuration;

    /** @var DocumentService */
    private $documentService;

    const KEY = 'imagesdeliverynote';

    public function __construct(
        DocumentGeneratorInterface $deliveryNoteGenerator,
        DocumentService $documentService,
        Configuration $configuration
    ) {
        $this->decoratedService = $deliveryNoteGenerator;
        $this->configuration = $configuration;
        $this->documentService = $documentService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(): string
    {
        return $this->decoratedService->supports();
    }

    /**
     * {@inheritdoc}
     */
    public function generate(
        OrderEntity $order,
        DocumentConfiguration $config,
        Context $context,
        ?string $templatePath = null
    ): string {
        if (true === $active = (bool)$this->configuration->getPluginConfig(self::KEY)) {
            $config->assign(
                [
                    'invIsImageDeliveryNoteActive' => $active,
                    'invImageSize' => (int)$this->configuration->getPluginConfig('imagesize')
                ]
            );
            $this->documentService->extendLineItems($context, $order->getLineItems());
        }
        return $this->decoratedService->generate($order, $config, $context, $templatePath);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileName(DocumentConfiguration $config): string
    {
        return $this->decoratedService->getFileName($config);
    }
}
