<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\PayPal\Webhook\Handler;

use Shopware\Core\Framework\Context;
use Swag\PayPal\RestApi\PayPalApiStruct;
use Swag\PayPal\RestApi\V2\Api\Order\PurchaseUnit\Payments\Capture;
use Swag\PayPal\RestApi\V2\Api\Webhook as WebhookV2;
use Swag\PayPal\Webhook\Exception\WebhookException;
use Swag\PayPal\Webhook\WebhookEventTypes;

class CaptureDenied extends AbstractWebhookHandler
{
    public function getEventType(): string
    {
        return WebhookEventTypes::PAYMENT_CAPTURE_DENIED;
    }

    /**
     * @param WebhookV2 $webhook
     */
    public function invoke(PayPalApiStruct $webhook, Context $context): void
    {
        /** @var Capture|null $capture */
        $capture = $webhook->getResource();
        if ($capture === null) {
            throw new WebhookException($this->getEventType(), 'Given webhook does not have needed resource data');
        }
        $orderTransaction = $this->getOrderTransactionV2($capture, $context);

        $this->orderTransactionStateHandler->cancel($orderTransaction->getId(), $context);
    }
}
