<?php declare(strict_types=1);

namespace Fgits\AutoInvoice\Storefront\Controller;

use Fgits\AutoInvoice\Service\Document;
use Shopware\Core\Checkout\Customer\SalesChannel\AccountService;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\AccountProfileController;
use Shopware\Storefront\Page\Account\Overview\AccountOverviewPageLoader;
use Shopware\Storefront\Page\Account\Profile\AccountProfilePageLoader;
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
class CustomAccountProfileController extends AccountProfileController
{
    /**
     * @var AccountOverviewPageLoader $pageLoader
     */
    private $pageLoader;

    /**
     * @var Document $document
     */
    private $document;

    public function __construct(
        AccountOverviewPageLoader $overviewPageLoader,
        AccountProfilePageLoader $profilePageLoader,
        AccountService $accountService,
        Document $document
    ) {
        parent::__construct($overviewPageLoader, $profilePageLoader, $accountService);

        $this->pageLoader = $overviewPageLoader;
        $this->document = $document;
    }

    /**
     * Overrides AccountProfileController::index() to add
     * an invoice download button. Works in conjunction with
     * src/Resources/views/storefront/page/account/order-history/order-detail.html.twig.
     *
     * @Route("/account", name="frontend.account.home.page", methods={"GET"})
     *
     * @param Request $request
     * @param SalesChannelContext $context
     *
     * @return Response
     */
    public function index(Request $request, SalesChannelContext $context): Response
    {
        $this->denyAccessUnlessLoggedIn();

        $page = $this->pageLoader->load($request, $context);

        $order = $page->getNewestOrder();

        if ($order instanceof OrderEntity) {
            try {
                $order->invoice = $this->document->getInvoice($order);
            } catch (\Exception $e) {
                $order->invoice = [];
            }
        }

        return $this->renderStorefront('@Storefront/storefront/page/account/index.html.twig', ['page' => $page]);
    }
}
