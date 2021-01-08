<?php

namespace Fgits\AutoInvoice\Twig;

use Fgits\AutoInvoice\Service\Document;
use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 */
class TwigExtension extends AbstractExtension
{
    /**
     * @var Document $document
     */
    private $document;

    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @var SystemConfigService $systemConfigService
     */
    private $systemConfigService;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * TwigExtension constructor.
     *
     * @param Document $document
     * @param ContainerInterface $container
     * @param SystemConfigService $systemConfigService
     * @param LoggerInterface $logger
     */
    public function __construct(
        Document $document,
        ContainerInterface $container,
        SystemConfigService $systemConfigService,
        LoggerInterface $logger
    ) {
        $this->document            = $document;
        $this->container           = $container;
        $this->systemConfigService = $systemConfigService;
        $this->logger              = $logger;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('fgits_autoinvoice_downloadable_invoice', [$this, 'downloadableInvoice'])
        ];
    }

    public function downloadableInvoice(OrderEntity $order): bool
    {
        $config = $this->systemConfigService->get('fgitsAutoinvoiceSW6.config', $order->getSalesChannelId());

        if (!$config['enableAccountDownload']) {
            return false;
        }

        try {
            $order->fgitsInvoice = $this->document->getInvoice($order);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
