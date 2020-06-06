<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Response\GetOrder;

use DateTime;
use KlarnaPayment\Components\Client\Response\GenericResponse;
use KlarnaPayment\Components\Client\Response\GetOrderResponse;
use KlarnaPayment\Components\Client\Struct\LineItem;
use KlarnaPayment\Components\Client\Struct\ProductIdentifier;
use RuntimeException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\Currency\CurrencyEntity;

class GetOrderResponseHydrator implements GetOrderResponseHydratorInterface
{
    /** @var EntityRepositoryInterface */
    private $currencyRepository;

    public function __construct(EntityRepositoryInterface $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
    }

    public function hydrate(GenericResponse $genericResponse, Context $context): GetOrderResponse
    {
        $order = $genericResponse->getResponse();

        $expiryDate = DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $order['expires_at']);
        $precision  = $this->getCurrencyPrecision($order['purchase_currency'], $context);

        $response = new GetOrderResponse();
        $response->assign([
            'orderId'              => $order['order_id'],
            'orderNumber'          => $order['merchant_reference1'],
            'fraudStatus'          => $order['fraud_status'],
            'orderStatus'          => $order['status'],
            'currency'             => $order['purchase_currency'],
            'decimalPrecision'     => $precision,
            'orderAmount'          => $order['order_amount'],
            'expiryDate'           => $expiryDate,
            'reference'            => $order['klarna_reference'],
            'capturedAmount'       => $order['captured_amount'],
            'remainingAmount'      => $order['remaining_authorized_amount'],
            'refundedAmount'       => $order['refunded_amount'],
            'orderLines'           => $this->hydrateOrderLines($order, $precision),
            'initialPaymentMethod' => $order['initial_payment_method']['description'],
        ]);

        return $response;
    }

    private function getCurrencyPrecision(string $currency, Context $context): int
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('currency.isoCode', $currency));

        /** @var null|CurrencyEntity $currencyEntity */
        $currencyEntity = $this->currencyRepository->search($criteria, $context)->first();

        if (null === $currencyEntity) {
            throw new RuntimeException('could not find currency entity for currency: ' . $currency);
        }

        return $currencyEntity->getDecimalPrecision();
    }

    /**
     * @return LineItem[]
     */
    private function hydrateOrderLines(array $order, int $precision): array
    {
        $lineItems = [];

        foreach ($order['order_lines'] as $orderLine) {
            $lineItem = new LineItem();

            if (!empty($orderLine['product_identifiers'])) {
                $productIdentifier = new ProductIdentifier();
                $productIdentifier->assign([
                    'brand'                  => $orderLine['product_identifiers']['brand'] ?? null,
                    'categoryPath'           => $orderLine['product_identifiers']['category_path'] ?? null,
                    'globalTradeItemNumber'  => $orderLine['product_identifiers']['global_trade_item_number'] ?? null,
                    'manufacturerPartNumber' => $orderLine['product_identifiers']['manufacturer_part_number'] ?? null,
                ]);

                $lineItem->assign([
                    'productIdentifier' => $productIdentifier,
                ]);
            }

            $capturedQuantity = $this->getProcessedQuantity($orderLine, $order['captures']);
            $refundedQuantity = $this->getProcessedQuantity($orderLine, $order['refunds']);

            $lineItem->assign([
                'reference'        => $orderLine['reference'],
                'type'             => $orderLine['type'],
                'quantity'         => $orderLine['quantity'],
                'capturedQuantity' => $capturedQuantity,
                'refundedQuantity' => $refundedQuantity,
                'quantityUnit'     => $orderLine['quantity_unit'] ?? null,
                'name'             => $orderLine['name'],
                'precision'        => $precision,
                'totalAmount'      => $orderLine['total_amount'] / (10 ** $precision),
                'unitPrice'        => $orderLine['unit_price'] / (10 ** $precision),
                'taxRate'          => $orderLine['tax_rate'] / 100,
                'totalTaxAmount'   => $orderLine['total_tax_amount'] / (10 ** $precision),
            ]);

            $lineItems[] = $lineItem;
        }

        return $lineItems;
    }

    private function getProcessedQuantity(array $orderLine, array $elements): int
    {
        $quantity = 0;

        if (empty($elements)) {
            return $quantity;
        }

        $filter = static function (array $capturedOrderLine) use ($orderLine) {
            return $orderLine['reference'] === $capturedOrderLine['reference'];
        };

        foreach ($elements as $capture) {
            /** @var array $orderLines */
            $orderLines = array_filter($capture['order_lines'], $filter);

            foreach ($orderLines as $capturedOrderLine) {
                $quantity += $capturedOrderLine['quantity'];
            }
        }

        if ($quantity > $orderLine['quantity']) {
            $quantity = $orderLine['quantity'];
        }

        return $quantity;
    }
}
