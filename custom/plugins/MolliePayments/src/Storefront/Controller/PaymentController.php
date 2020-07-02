<?php

namespace Kiener\MolliePayments\Storefront\Controller;

use Exception;
use Kiener\MolliePayments\Helper\DeliveryStateHelper;
use Kiener\MolliePayments\Helper\PaymentStatusHelper;
use Kiener\MolliePayments\Service\CustomFieldService;
use Kiener\MolliePayments\Service\LoggerService;
use Kiener\MolliePayments\Service\SettingsService;
use Kiener\MolliePayments\Setting\MollieSettingStruct;
use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Order;
use Mollie\Api\Types\PaymentStatus;
use RuntimeException;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class PaymentController extends StorefrontController
{
    /** @var RouterInterface */
    private $router;

    /** @var EntityRepository */
    private $orderTransactionRepository;

    /** @var MollieApiClient */
    private $apiClient;

    /** @var DeliveryStateHelper */
    private $deliveryStateHelper;

    /** @var PaymentStatusHelper */
    private $paymentStatusHelper;

    /** @var SettingsService */
    private $settingsService;

    /** @var LoggerService */
    private $logger;

    public function __construct(
        RouterInterface $router,
        EntityRepository $orderTransactionRepository,
        MollieApiClient $apiClient,
        DeliveryStateHelper $deliveryStateHelper,
        PaymentStatusHelper $paymentStatusHelper,
        SettingsService $settingsService,
        LoggerService $logger
    )
    {
        $this->router = $router;
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->apiClient = $apiClient;
        $this->deliveryStateHelper = $deliveryStateHelper;
        $this->paymentStatusHelper = $paymentStatusHelper;
        $this->settingsService = $settingsService;
        $this->logger = $logger;
    }

    /**
     * @RouteScope(scopes={"storefront"})
     * @Route("/mollie/payment/{transactionId}", defaults={"csrf_protected"=false}, name="frontend.mollie.payment",
     *                                           options={"seo"="false"}, methods={"GET", "POST"})
     *
     * @param SalesChannelContext $context
     * @param                     $transactionId
     *
     * @return Response|RedirectResponse
     * @throws ApiException
     */
    public function payment(SalesChannelContext $context, $transactionId): ?Response
    {
        $criteria = null;
        $customFields = null;
        $errorMessage = null;
        $mollieOrder = null;
        $mollieOrderId = null;
        $order = null;
        $paymentFailed = false;
        $paymentStatus = null;
        $redirectUrl = null;
        $transaction = null;

        /** @var MollieSettingStruct $settings */
        $settings = $this->settingsService->getSettings(
            $context->getSalesChannel()->getId(),
            $context->getContext()
        );

        // Add a message to the log that the webhook has been triggered.
        if ($settings->isDebugMode()) {
            $this->logger->addEntry(
                sprintf('Payment return for transaction %s is triggered.', $transactionId),
                $context->getContext(),
                null,
                [
                    'transactionId' => $transactionId,
                ]
            );
        }

        /**
         * Create a search criteria to find the transaction by it's ID in the
         * transaction repository.
         *
         * @var $criteria
         */
        try {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('id', $transactionId));
            $criteria->addAssociation('order');
        } catch (InconsistentCriteriaIdsException $e) {
            $errorMessage = $errorMessage ?? $e->getMessage();
        }

        /**
         * Get the transaction from the order transaction repository. With the
         * transaction we can fetch the order from the database.
         *
         * @var OrderTransactionEntity $transaction
         */
        if ($criteria !== null) {
            try {
                $transaction = $this->orderTransactionRepository->search($criteria, $context->getContext())->first();
            } catch (Exception $e) {
                $errorMessage = $errorMessage ?? $e->getMessage();
            }
        }

        /**
         * Get the order entity from the transaction. With the order entity, we can
         * retrieve the Mollie ID from it's custom fields and fetch the payment
         * status from Mollie's Orders API.
         *
         * @var OrderEntity $order
         */
        if ($transaction !== null) {
            $order = $transaction->getOrder();
        }

        /**
         * Get the custom fields from the order. These custom fields are used to
         * retrieve the order ID of Mollie's order. With this ID, we can fetch the
         * order from Mollie's Orders API.
         *
         * @var $customFields
         */
        if ($order !== null) {
            $customFields = $order->getCustomFields();
        } else {
            $errorMessage = $errorMessage ?? 'No order found for transaction with ID ' . $transactionId . '.';
        }

        /**
         * Set the API keys at Mollie based on the current context.
         */
        $this->setApiKeysBySalesChannelContext($context);

        /**
         * With the order ID from the custom fields, we fetch the order from Mollie's
         * Orders API.
         *
         * The transaction return URL is used for redirecting the customer to the checkout
         * finish page.
         *
         * @var $mollieOrder
         */
        if (is_array($customFields)) {
            if (isset($customFields[CustomFieldService::CUSTOM_FIELDS_KEY_MOLLIE_PAYMENTS]['order_id'])) {
                /** @var string $mollieOrderId */
                $mollieOrderId = $customFields[CustomFieldService::CUSTOM_FIELDS_KEY_MOLLIE_PAYMENTS]['order_id'];

                /** @var Order $mollieOrder */
                try {
                    $mollieOrder = $this->apiClient->orders->get($mollieOrderId, [
                        'embed' => 'payments'
                    ]);
                } catch (ApiException $e) {
                    $errorMessage = $errorMessage ?? $e->getMessage();
                }
            }

            if (isset($customFields[CustomFieldService::CUSTOM_FIELDS_KEY_MOLLIE_PAYMENTS]['transactionReturnUrl'])) {
                $redirectUrl = $customFields[CustomFieldService::CUSTOM_FIELDS_KEY_MOLLIE_PAYMENTS]['transactionReturnUrl'];
            }
        }

        /**
         * The payment status of the order is fetched from Mollie's Orders API. We
         * use this payment status to set the status in Shopware.
         */
        if ($mollieOrder !== null) {
            try {
                $paymentStatus = $this->paymentStatusHelper->processPaymentStatus(
                    $transaction,
                    $order,
                    $mollieOrder,
                    $context->getContext()
                );
            } catch (Exception $e) {
                $errorMessage = $errorMessage ?? $e->getMessage();
            }
        } else {
            $errorMessage = $errorMessage ?? 'No order found in the Orders API with ID ' . $mollieOrderId ?? '<unknown>';
        }

        /**
         * If the payment status is null, no status could be set.
         */
        if ($paymentStatus === null) {
            $errorMessage = $errorMessage ?? 'The payment status has not been set for order with ID ' . $mollieOrderId ?? '<unknown>';
        }

        if (
            $paymentStatus !== null
            && (
                $paymentStatus === PaymentStatus::STATUS_CANCELED
                || $paymentStatus === PaymentStatus::STATUS_FAILED
            )
        ) {
            $paymentFailed = true;
            $mollieOrder->createPayment([]);

            if ($mollieOrder->getCheckoutUrl() !== null) {
                // Reopen the order transaction
                try {
                    $this->paymentStatusHelper->getOrderTransactionStateHandler()->reopen(
                        $transactionId,
                        $context->getContext()
                    );
                } catch (Exception $e) {
                    $this->logger->addEntry(
                        $e->getMessage(),
                        $context->getContext(),
                        $e,
                        [
                            'function' => 'payment-set-transaction-state'
                        ]
                    );
                }

                $redirectUrl = $mollieOrder->getCheckoutUrl();
            }
        }

        /**
         * If any errors occurred during the webhook call, we return an error message.
         */
        if ($errorMessage !== null) {
            $this->logger->addEntry(
                $errorMessage,
                $context->getContext(),
                null,
                [
                    'function' => 'payment',
                ]
            );
        }

        // If the payment failed, render a storefront to let the customer know
        if ($paymentFailed === true && (string) $redirectUrl !== '') {

            // If we redirect to the payment screen, set the transaction to in progress
            try {
                $this->paymentStatusHelper->getOrderTransactionStateHandler()->process(
                    $transactionId,
                    $context->getContext()
                );
            } catch (Exception $e) {
                $this->logger->addEntry(
                    $e->getMessage(),
                    $context->getContext(),
                    $e,
                    [
                        'function' => 'payment-set-transaction-state'
                    ]
                );
            }

            return $this->renderStorefront('@Storefront/storefront/page/checkout/payment/failed.html.twig', [
                'redirectUrl' => $redirectUrl
            ]);
        }

        return new RedirectResponse($redirectUrl);
    }

    /**
     * Sets the API keys for Mollie based on the current context.
     *
     * @param SalesChannelContext $context
     *
     * @throws ApiException
     */
    private function setApiKeysBySalesChannelContext(SalesChannelContext $context): void
    {
        try {
            /** @var MollieSettingStruct $settings */
            $settings = $this->settingsService->getSettings($context->getSalesChannel()->getId());

            /** @var string $apiKey */
            $apiKey = $settings->isTestMode() === false ? $settings->getLiveApiKey() : $settings->getTestApiKey();

            // Log the used API keys
            if ($settings->isDebugMode()) {
                $this->logger->addEntry(
                    sprintf('Selected API key %s for sales channel %s (%s)', $apiKey, $context->getSalesChannel()->getName(), $settings->isTestMode() ? 'test-mode' : 'live-mode'),
                    $context->getContext(),
                    null,
                    [
                        'apiKey' => $apiKey,
                    ]
                );
            }

            // Set the API key
            $this->apiClient->setApiKey($apiKey);
        } catch (InconsistentCriteriaIdsException $e) {
            $this->logger->addEntry(
                $e->getMessage(),
                $context->getContext(),
                $e,
                [
                    'function' => 'set-mollie-api-key',
                ]
            );

            throw new RuntimeException(sprintf('Could not set Mollie Api Key, error: %s', $e->getMessage()));
        }
    }
}