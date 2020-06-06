<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\PayPal\Test\PayPal\Resource;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Swag\PayPal\Payment\PayPalPaymentHandler;
use Swag\PayPal\PayPal\Api\Refund;
use Swag\PayPal\PayPal\PaymentStatus;
use Swag\PayPal\PayPal\Resource\SaleResource;
use Swag\PayPal\Test\Helper\ServicesTrait;
use Swag\PayPal\Test\Mock\PayPal\Client\_fixtures\GetSaleResponseFixture;

class SaleResourceTest extends TestCase
{
    use ServicesTrait;

    public function testGet(): void
    {
        $saleResponse = $this->createSaleResource()->get(
            'saleId',
            Defaults::SALES_CHANNEL
        );

        $sale = \json_encode($saleResponse);
        static::assertNotFalse($sale);

        $saleArray = \json_decode($sale, true);

        static::assertSame(GetSaleResponseFixture::ID, $saleArray['id']);
    }

    public function testRefund(): void
    {
        $refund = new Refund();
        $refundResponse = $this->createSaleResource()->refund(
            PayPalPaymentHandler::PAYPAL_REQUEST_PARAMETER_PAYMENT_ID,
            $refund,
            Defaults::SALES_CHANNEL
        );

        static::assertSame(PaymentStatus::PAYMENT_COMPLETED, $refundResponse->getState());
    }

    private function createSaleResource(): SaleResource
    {
        return new SaleResource(
            $this->createPayPalClientFactory()
        );
    }
}
