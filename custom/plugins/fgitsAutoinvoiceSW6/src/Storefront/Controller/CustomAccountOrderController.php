<?php declare(strict_types = 1);

namespace Fgits\AutoInvoice\Storefront\Controller;

use Fgits\AutoInvoice\Service\Document;
use Shopware\Core\Checkout\Order\SalesChannel\AbstractCancelOrderRoute;
use Shopware\Core\Checkout\Order\SalesChannel\AbstractOrderRoute;
use Shopware\Core\Checkout\Order\SalesChannel\AbstractSetPaymentOrderRoute;
use Shopware\Core\Checkout\Payment\SalesChannel\AbstractHandlePaymentMethodRoute;
use Shopware\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannel\ContextSwitchRoute;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\AccountOrderController;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPageLoader;
use Shopware\Storefront\Page\Account\Order\AccountOrderPageLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Copyright (c) 2020. GOLLE IT.
 *
 * @author Fabian Golle <fabian@golle-it.de>
 *
 * @RouteScope(scopes={"storefront"})
 */
class CustomAccountOrderController extends AccountOrderController
{
    /**
     * @var AccountOrderPageLoader $pageLoader
     */
    private $pageLoader;

    /**
     * @var Document $document
     */
    private $document;

    /**
     * CustomAccountOrderController constructor.
     *
     * @param AccountOrderPageLoader $orderPageLoader
     * @param AbstractOrderRoute $orderRoute
     * @param RequestCriteriaBuilder $requestCriteriaBuilder
     * @param AccountEditOrderPageLoader $accountEditOrderPageLoader
     * @param ContextSwitchRoute $contextSwitchRoute
     * @param AbstractCancelOrderRoute $orderStateChangeRoute
     * @param AbstractSetPaymentOrderRoute $setPaymentOrderRoute
     * @param AbstractHandlePaymentMethodRoute $handlePaymentMethodRoute
     * @param EventDispatcherInterface $eventDispatcher
     * @param Document $document
     */
    public function __construct(
        AccountOrderPageLoader $orderPageLoader,
        AbstractOrderRoute $orderRoute,
        RequestCriteriaBuilder $requestCriteriaBuilder,
        AccountEditOrderPageLoader $accountEditOrderPageLoader,
        ContextSwitchRoute $contextSwitchRoute,
        AbstractCancelOrderRoute $orderStateChangeRoute,
        AbstractSetPaymentOrderRoute $setPaymentOrderRoute,
        AbstractHandlePaymentMethodRoute $handlePaymentMethodRoute,
        EventDispatcherInterface $eventDispatcher,
        Document $document
    ) {
        parent::__construct(
            $orderPageLoader,
            $orderRoute,
            $requestCriteriaBuilder,
            $accountEditOrderPageLoader,
            $contextSwitchRoute,
            $orderStateChangeRoute,
            $setPaymentOrderRoute,
            $handlePaymentMethodRoute,
            $eventDispatcher
        );

        $this->pageLoader = $orderPageLoader;
        $this->document   = $document;
    }

    /**
     * Overrides AccountOrderController::orderOverview() to add
     * an invoice download button. Works in conjunction with
     * src/Resources/views/storefront/page/account/order-history/order-detail.html.twig.
     *
     * @Route("/account/order", name="frontend.account.order.page", options={"seo"="false"}, methods={"GET"})
     *
     * @param Request $request
     * @param SalesChannelContext $context
     *
     * @return Response
     */
    public function orderOverview(Request $request, SalesChannelContext $context): Response
    {
        $this->denyAccessUnlessLoggedIn();

        $page = $this->pageLoader->load($request, $context);

        foreach ($page->getOrders() as $order)
        {
            try {
                $order->invoice = $this->document->getInvoice($order);
            } catch (\Exception $e) {
                $order->invoice = [];
            }
        }

        return $this->renderStorefront('@Storefront/storefront/page/account/order-history/index.html.twig', ['page' => $page]);
    }
}
