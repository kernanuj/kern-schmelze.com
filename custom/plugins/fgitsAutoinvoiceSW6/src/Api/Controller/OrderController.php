<?php declare(strict_types=1);

namespace Fgits\AutoInvoice\Api\Controller;

use Fgits\AutoInvoice\Service\DB\Order;
use Fgits\AutoInvoice\Service\DocumentCreator;
use Fgits\AutoInvoice\Service\Export;
use Fgits\AutoInvoice\Service\FgitsLibrary\ScheduledTask as FgitsScheduledTask;
use Fgits\AutoInvoice\Service\OrderProcessor;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Andrey Grigorkin <andrey@golle-it.de>
 *
 * @RouteScope(scopes={"api"})
 */
class OrderController extends AbstractController
{
    /**
     * @var Order $order
     */
    private $order;

    /**
     * @var Export $export
     */
    private $export;

    /**
     * @var EntityRepositoryInterface $orderRepository
     */
    private $orderRepository;

    /**
     * @var DocumentCreator $documentCreator
     */
    private $documentCreator;

    /**
     * @var OrderProcessor $orderProcessor
     */
    private $orderProcessor;

    /**
     * @var FgitsScheduledTask $scheduledTask
     */
    private $scheduledTask;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * OrderController constructor.
     *
     * @param Order $order
     * @param Export $export
     * @param EntityRepositoryInterface $orderRepository
     * @param DocumentCreator $documentCreator
     * @param OrderProcessor $orderProcessor
     * @param FgitsScheduledTask $scheduledTask
     * @param LoggerInterface $logger
     */
    public function __construct(
        Order $order,
        Export $export,
        EntityRepositoryInterface $orderRepository,
        DocumentCreator $documentCreator,
        OrderProcessor $orderProcessor,
        FgitsScheduledTask $scheduledTask,
        LoggerInterface $logger
    ) {
        $this->order           = $order;
        $this->export          = $export;
        $this->orderRepository = $orderRepository;
        $this->documentCreator = $documentCreator;
        $this->orderProcessor  = $orderProcessor;
        $this->scheduledTask   = $scheduledTask;
        $this->logger          = $logger;
    }

    /**
     * @Route("/api/v{version}/fgits/autoinvoice/order/{orderId}/invoice/send", name="api.action.fgits.autoinvoice.invoice.send", methods={"GET"})
     *
     * @param Request $request
     * @param Context $context
     * @param string $orderId
     *
     * @return JsonResponse
     */
    public function sendInvoice(Request $request, Context $context, string $orderId): JsonResponse
    {
        try {
            $order = $this->order->getOrderById($orderId, $context);

            $this->documentCreator->createInvoice($order);

            $this->orderProcessor->sendCustomerEmail($order);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => $e->getMessage()]);
        }

        return new JsonResponse(['status' => 'OK']);
    }

    /**
     * @Route("/api/v{version}/fgits/autoinvoice/order/invoices/export", name="api.action.fgits.autoinvoice.invoices.export", methods={"GET"})
     *
     * @param Request $request
     * @param Context $context
     *
     * @return JsonResponse
     */
    public function exportInvoices(Request $request, Context $context): JsonResponse
    {
        try {
            $this->export->export();
        } catch (\Exception $e) {
            return new JsonResponse(['status' => $e->getMessage()]);
        }

        return new JsonResponse(['status' => 'OK']);
    }
}
