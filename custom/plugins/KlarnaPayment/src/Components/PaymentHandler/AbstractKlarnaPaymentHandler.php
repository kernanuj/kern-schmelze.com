<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\PaymentHandler;

use KlarnaPayment\Components\Client\Response\GenericResponse;
use LogicException;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractKlarnaPaymentHandler
{
    /** @var EntityRepositoryInterface */
    protected $transactionRepository;

    /** @var RequestStack */
    protected $requestStack;

    protected function saveTransactionData(OrderTransactionEntity $transaction, GenericResponse $response, Context $context): void
    {
        $customFields = $transaction->getCustomFields() ?? [];

        $customFields = array_merge($customFields, [
            'klarna_order_id'     => $response->getResponse()['order_id'],
            'klarna_fraud_status' => $response->getResponse()['fraud_status'],
        ]);

        $update = [
            'id'           => $transaction->getId(),
            'customFields' => $customFields,
        ];

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($update): void {
            $this->transactionRepository->update([$update], $context);
        });
    }

    protected function fetchRequestData(): RequestDataBag
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new LogicException('missing current request');
        }

        return new RequestDataBag($request->request->all());
    }
}
