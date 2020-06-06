<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Request\CreateRefund;

use KlarnaPayment\Components\Client\Request\CreateRefundRequest;
use KlarnaPayment\Components\Client\Struct\LineItem;
use KlarnaPayment\Components\Client\Struct\ProductIdentifier;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;

class CreateRefundRequestHydrator implements CreateRefundRequestHydratorInterface
{
    public function hydrate(RequestDataBag $dataBag, Context $context): CreateRefundRequest
    {
        $precision    = (int) $dataBag->get('decimalPrecision');
        $refundAmount = (float) $dataBag->get('refundAmount');

        $orderLines = json_decode($dataBag->get('orderLines'), true);

        $request = new CreateRefundRequest();
        $request->assign([
            'salesChannel' => $dataBag->get('salesChannel'),
            'orderId'      => $dataBag->get('klarna_order_id'),
            'precision'    => $precision,
            'refundAmount' => $refundAmount,
            'reference'    => Uuid::randomHex(),
            'orderLines'   => $this->hydrateOrderLines($orderLines),
        ]);

        if (!empty($dataBag->get('description'))) {
            $request->assign([
                'description' => substr($dataBag->get('description'), 0, 255),
            ]);
        }

        return $request;
    }

    /**
     * @return LineItem[]
     */
    private function hydrateOrderLines(array $order_lines): array
    {
        $lineItems = [];

        foreach ($order_lines as $order_line) {
            $lineItem = new LineItem();

            if (!empty($order_line['product_identifiers'])) {
                $productIdentifier = new ProductIdentifier();
                $productIdentifier->assign([
                    'brand'                  => $order_line['product_identifiers']['brand'] ?? null,
                    'categoryPath'           => $order_line['product_identifiers']['category_path'] ?? null,
                    'globalTradeItemNumber'  => $order_line['product_identifiers']['global_trade_item_number'] ?? null,
                    'manufacturerPartNumber' => $order_line['product_identifiers']['manufacturer_part_number'] ?? null,
                ]);

                $lineItem->assign([
                    'productIdentifier' => $productIdentifier,
                ]);
            }

            $lineItem->assign([
                'reference'      => $order_line['reference'],
                'type'           => $order_line['type'],
                'quantity'       => $order_line['quantity'],
                'quantityUnit'   => $order_line['quantity_unit'] ?? null,
                'name'           => $order_line['name'],
                'totalAmount'    => $order_line['total_amount'],
                'unitPrice'      => $order_line['unit_price'],
                'taxRate'        => $order_line['tax_rate'],
                'totalTaxAmount' => $order_line['total_tax_amount'],
            ]);

            $lineItems[] = $lineItem;
        }

        return $lineItems;
    }
}
