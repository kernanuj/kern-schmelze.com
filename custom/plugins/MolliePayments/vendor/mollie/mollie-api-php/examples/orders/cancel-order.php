<?php
/*
 * Cancel an order using the Mollie API.
 */

use Mollie\Api\Exceptions\ApiException;

try {
    /*
     * Initialize the Mollie API library with your API key or OAuth access token.
     */
    require "../initialize.php";

    /*
     * Cancel the order with ID "ord_pbjz8x"
     *
     * See: https://docs.mollie.com/reference/v2/orders-api/cancel-order
     */
    $order = $mollie->orders->get("ord_pbjz8x");
    if ($order->isCancelable) {
        $canceledOrder = $order->cancel();
        echo "Your order " . $order->id . " has been canceled.";
    } else {
        echo "Unable to cancel your order " . $order->id . ".";
    }
} catch ( ApiException $e) {
    echo "API call failed: " . htmlspecialchars($e->getMessage());
}
