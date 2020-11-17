<?php declare(strict_types=1);
/**
 * Copyright (c) Web Loupe. All rights reserved.
 * This file is part of software that is released under a proprietary license.
 * You must not copy, modify, distribute, make publicly available, or execute
 * its contents or parts thereof without express permission by the copyright
 * holder, unless otherwise permitted by law.
 */

namespace Welo\AddressDocuments\Service\Decorator;

use Shopware\Core\Checkout\Document\DocumentConfiguration;
use Shopware\Core\Checkout\Document\DocumentGenerator\DocumentGeneratorInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Welo\AddressDocuments\Service\Configuration;

/**
 * Class StornoGeneratorDecorated
 *
 * @author    Cyprien Nkeneng <cyprien.nkeneng@webloupe.de>
 * @copyright Copyright (c) 2017-2020 WEB LOUPE
 * @package   Welo\AddressDocuments\Service\Decorator
 * @version   1
 */
class StornoGeneratorDecorated implements DocumentGeneratorInterface
{
    /** @var DocumentGeneratorInterface  */
    private $decoratedService;

    /**
     * @var Configuration
     */
    private $configuration;

    const DOCUMENT_NAME = 'WeloAddressDocument6StornoCancellation';

    /**
     * InvoiceGeneratorDecorated constructor.
     *
     * @param DocumentGeneratorInterface $stornoGenerator
     * @param Configuration $configuration
     */
    public function __construct(
        DocumentGeneratorInterface $stornoGenerator,
        Configuration $configuration
    ) {
        $this->decoratedService = $stornoGenerator;
        $this->configuration = $configuration;
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
        $order->assign(['shippingAddress' => $order->getDeliveries()->getShippingAddress()->first()]);
        $config->assign(['weloIsStornoCancellationActive' => (bool)$this->configuration->getPluginConfig(self::DOCUMENT_NAME)]);
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
