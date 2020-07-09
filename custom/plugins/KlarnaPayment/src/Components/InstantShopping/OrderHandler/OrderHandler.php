<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\InstantShopping\OrderHandler;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\PaymentService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderHandler implements OrderHandlerInterface
{
    /** @var CartService */
    private $cartService;

    /** @var EntityRepositoryInterface */
    private $orderRepository;

    /** @var Router */
    private $router;

    /** @var PaymentService */
    private $paymentService;

    public function __construct(
        CartService $cartService,
        EntityRepositoryInterface $orderRepository,
        Router $router,
        PaymentService $paymentService
    ) {
        $this->cartService     = $cartService;
        $this->orderRepository = $orderRepository;
        $this->router          = $router;
        $this->paymentService  = $paymentService;
    }

    public function createOrder(Cart $cart, Request $request, RequestDataBag $dataBag, SalesChannelContext $context): ?RedirectResponse
    {
        $orderId = $this->cartService->order($cart, $context);

        $this->addAffiliateTracking($orderId, $request, $context);
        $finishUrl = $this->router->generate('frontend.checkout.finish.page', [
            'orderId' => $orderId,
        ], UrlGeneratorInterface::ABSOLUTE_PATH);

        return $this->paymentService->handlePaymentByOrder($orderId, $dataBag, $context, $finishUrl);
    }

    public function getOrderByOrderAndTransactionId(string $orderId, string $transactionId, SalesChannelContext $context): OrderEntity
    {
        $criteria = new Criteria();
        $criteria->addAssociation('transactions');
        $criteria->addAssociation('orderCustomer.customer');
        $criteria->addFilter(new EqualsFilter('id', $orderId));
        $criteria->addFilter(new EqualsFilter('transactions.id', $transactionId));

        return $this->orderRepository->search($criteria, $context->getContext())->first();
    }

    private function addAffiliateTracking(string $orderId, Request $request, SalesChannelContext $context): void
    {
        if ($request->getSession()->get('affiliateCode') && $request->getSession()->get('campaignCode')) {
            $this->orderRepository->update([[
                'id'            => $orderId,
                'affiliateCode' => $request->getSession()->get('affiliateCode'),
                'campaignCode'  => $request->getSession()->get('campaignCode'),
            ]], $context->getContext());
        }
    }
}
