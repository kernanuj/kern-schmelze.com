<?php declare(strict_types = 1);

namespace InvProductImageDocuments\Service\Decorator;

use Shopware\Core\Checkout\Document\DocumentConfiguration;
use Shopware\Core\Checkout\Document\DocumentGenerator\DocumentGeneratorInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use InvProductImageDocuments\Service\Configuration;
use InvProductImageDocuments\Service\DocumentService;

/**
 * Class InvoiceGeneratorDecorated
 *
 * @author    Nils Harder <hallo@inventivo.de>
 * @copyright Copyright (c) 2020 Nils Harder
 * @package   InvProductImageDocuments\Service\Decorator
 * @version   1
 */
class InvoiceGeneratorDecorated implements DocumentGeneratorInterface
{
    /** @var DocumentGeneratorInterface  */
    private $decoratedService;

    /** @var Configuration */
    private $configuration;

    /** @var DocumentService */
    private $documentService;

    const KEY = 'imagesinvoice';

    public function __construct(
        DocumentGeneratorInterface $invoiceGenerator,
        DocumentService $documentService,
        Configuration $configuration
    ) {
        $this->decoratedService = $invoiceGenerator;
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
                    'invIsImageInvoiceActive' => 1,
                    'invImageSize' => (int) $this->configuration->getPluginConfig('imagesize')
                ]
            );
            $this->documentService->extendLineItems($context, $order->getLineItems());
        }
        return $this->decoratedService->generate($order, $config, $context, $templatePath);
    }

    public function getFileName(DocumentConfiguration $config): string
    {
        return $this->decoratedService->getFileName($config);
    }
}
