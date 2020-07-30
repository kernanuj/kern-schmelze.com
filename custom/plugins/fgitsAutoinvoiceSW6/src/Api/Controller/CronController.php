<?php declare(strict_types=1);

namespace Fgits\AutoInvoice\Api\Controller;

use Fgits\AutoInvoice\ScheduledTask\AutoInvoiceOrderScanTask;
use Fgits\AutoInvoice\Service\FgitsLibrary\ScheduledTask as FgitsScheduledTask;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Fabian Golle <fabian@golle-it.de>
 *
 * @RouteScope(scopes={"api"})
 */
class CronController extends AbstractController
{
    /**
     * @var FgitsScheduledTask $scheduledTask
     */
    private $scheduledTask;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * CronController constructor.
     *
     * @param FgitsScheduledTask $scheduledTask
     * @param LoggerInterface $logger
     */
    public function __construct(
        FgitsScheduledTask $scheduledTask,
        LoggerInterface $logger
    ) {
        $this->scheduledTask   = $scheduledTask;
        $this->logger          = $logger;
    }

    /**
     * @Route("/api/v{version}/fgits/autoinvoice/cron/activate", name="api.action.fgits.autoinvoice.cron.activate", methods={"GET"})
     *
     * @param Request $request
     * @param Context $context
     *
     * @return JsonResponse
     */
    public function activateCron(Request $request, Context $context): JsonResponse
    {
        $this->scheduledTask->schedule(AutoInvoiceOrderScanTask::getTaskName());

        return new JsonResponse(['status' => 'OK']);
    }
}
