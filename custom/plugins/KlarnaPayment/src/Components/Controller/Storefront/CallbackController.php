<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Controller\Storefront;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class CallbackController extends StorefrontController
{
    /** @var EntityRepositoryInterface */
    private $transactionRepository;

    /** @var OrderTransactionStateHandler */
    private $stateHandler;

    public function __construct(
        EntityRepositoryInterface $transactionRepository,
        OrderTransactionStateHandler $stateHandler
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->stateHandler          = $stateHandler;
    }

    /**
     * @Route("/klarna/callback/notification/{transaction_id}", defaults={"csrf_protected": false, "XmlHttpRequest": true}, name="frontend.klarna.callback.notification", methods={"POST"})
     */
    public function notificationCallback(Request $request, SalesChannelContext $context): Response
    {
        $criteria = new Criteria([$request->get('transaction_id')]);

        /** @var null|OrderTransactionEntity $transaction */
        $transaction = $this->transactionRepository->search($criteria, $context->getContext())->first();

        if (null === $transaction) {
            throw $this->createNotFoundException();
        }

        $event = (string) $request->get('event_type');

        if (stripos($event, 'FRAUD_RISK_') !== false) {
            $this->saveFraudStatus($transaction, $event, $context->getContext());
        }

        if ($event === 'FRAUD_RISK_REJECTED' || $event === 'FRAUD_RISK_STOPPED') {
            $this->stateHandler->cancel($transaction->getId(), $context->getContext());
        }

        return new Response();
    }

    private function saveFraudStatus(OrderTransactionEntity $transaction, string $event, Context $context): void
    {
        $fraudStatus  = str_replace('FRAUD_RISK_', '', $event);
        $customFields = $transaction->getCustomFields() ?? [];

        $customFields = array_merge($customFields, [
            'klarna_fraud_status' => $fraudStatus,
        ]);

        $update = [
            'id'           => $transaction->getId(),
            'customFields' => $customFields,
        ];

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($update): void {
            $this->transactionRepository->update([$update], $context);
        });
    }
}
