<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\PayPal\Test\Payment;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Exception\OrderNotFoundException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Swag\PayPal\Payment\Exception\RequiredParameterInvalidException;
use Swag\PayPal\Payment\PayPalPaymentController;
use Swag\PayPal\PayPal\Api\Payment\Transaction\RelatedResource;
use Swag\PayPal\PayPal\Resource\AuthorizationResource;
use Swag\PayPal\PayPal\Resource\CaptureResource;
use Swag\PayPal\PayPal\Resource\OrdersResource;
use Swag\PayPal\PayPal\Resource\SaleResource;
use Swag\PayPal\Test\Helper\ServicesTrait;
use Swag\PayPal\Test\Mock\PayPal\Client\_fixtures\GetPaymentSaleResponseFixture;
use Swag\PayPal\Test\Mock\PayPal\Client\_fixtures\GetResourceAuthorizeResponseFixture;
use Swag\PayPal\Test\Mock\PayPal\Client\_fixtures\GetResourceOrderResponseFixture;
use Swag\PayPal\Test\Mock\PayPal\Client\_fixtures\GetResourceSaleResponseFixture;
use Swag\PayPal\Test\Mock\PayPal\Client\_fixtures\RefundCaptureResponseFixture;
use Swag\PayPal\Test\Mock\PayPal\Client\_fixtures\RefundSaleResponseFixture;
use Swag\PayPal\Test\Mock\PayPal\Client\_fixtures\VoidAuthorizationResponseFixture;
use Swag\PayPal\Test\Mock\PayPal\Client\_fixtures\VoidOrderResponseFixture;
use Swag\PayPal\Test\Mock\Repositories\OrderRepositoryMock;
use Swag\PayPal\Test\Mock\Util\PaymentStatusUtilMock;
use Symfony\Component\HttpFoundation\Request;

class PayPalPaymentControllerTest extends TestCase
{
    use ServicesTrait;

    private const TEST_REFUND_CURRENCY = 'EUR';
    private const TEST_REFUND_DESCRIPTION = 'testDescription';
    private const TEST_REFUND_REASON = 'testReason';
    private const KEY_TO_TEST = 'keyToTest';
    private const VALUE_TO_TEST = 'valueToTest';

    public function testGetPaymentDetails(): void
    {
        $context = Context::createDefaultContext();
        $responseContent = $this->createPaymentController()->paymentDetails('testOrderId', 'testPaymentId', $context)->getContent();
        static::assertNotFalse($responseContent);

        $paymentDetails = \json_decode($responseContent, true);

        static::assertSame(
            GetPaymentSaleResponseFixture::TRANSACTION_AMOUNT_DETAILS_SUBTOTAL,
            $paymentDetails['transactions'][0]['amount']['details']['subtotal']
        );
    }

    public function testGetPaymentDetailsWithInvalidOrder(): void
    {
        $context = Context::createDefaultContext();
        $context->addExtension(OrderRepositoryMock::NO_ORDER, new ArrayStruct());

        $this->expectException(OrderNotFoundException::class);
        $this->expectExceptionMessage('Order with id "testOrderId" not found.');
        $this->createPaymentController()->paymentDetails('testOrderId', 'testPaymentId', $context)->getContent();
    }

    public function dataProviderTestResourceDetails(): array
    {
        return [
            [
                RelatedResource::AUTHORIZE,
                [
                    self::KEY_TO_TEST => 'id',
                    self::VALUE_TO_TEST => GetResourceAuthorizeResponseFixture::ID,
                ],
            ],
            [
                RelatedResource::CAPTURE,
                [
                    self::KEY_TO_TEST => 'is_final_capture',
                    self::VALUE_TO_TEST => true,
                ],
            ],
            [
                RelatedResource::ORDER,
                [
                    self::KEY_TO_TEST => 'id',
                    self::VALUE_TO_TEST => GetResourceOrderResponseFixture::ID,
                ],
            ],
            [
                RelatedResource::SALE,
                [
                    self::KEY_TO_TEST => 'id',
                    self::VALUE_TO_TEST => GetResourceSaleResponseFixture::ID,
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestResourceDetails
     */
    public function testResourceDetails(string $resourceType, array $assertions): void
    {
        $context = Context::createDefaultContext();
        $responseContent = $this->createPaymentController()->resourceDetails($context, $resourceType, 'testResourceId', 'testOrderId')->getContent();
        static::assertNotFalse($responseContent);

        $resource = \json_decode($responseContent, true);

        static::assertSame($assertions[self::VALUE_TO_TEST], $resource[$assertions[self::KEY_TO_TEST]]);
    }

    public function testResourceDetailsWithInvalidResourceType(): void
    {
        $context = Context::createDefaultContext();
        $this->expectException(RequiredParameterInvalidException::class);
        $this->expectExceptionMessage('Required parameter "resourceType" is missing or invalid');
        $this->createPaymentController()->resourceDetails($context, 'unknown', 'testResourceId', 'testOrderId')->getContent();
    }

    public function testRefundPayment(): void
    {
        $request = new Request();
        $context = Context::createDefaultContext();
        $responseContent = $this->createPaymentController()->refundPayment(
            $request,
            $context,
            RelatedResource::SALE,
            'testPaymentId',
            'testOrderId'
        )->getContent();
        static::assertNotFalse($responseContent);

        $refund = \json_decode($responseContent, true);

        static::assertSame(RefundSaleResponseFixture::REFUND_AMOUNT, $refund['amount']['total']);
    }

    public function testRefundCapture(): void
    {
        $responseContent = $this->createPaymentController()->refundPayment(
            new Request(),
            Context::createDefaultContext(),
            RelatedResource::CAPTURE,
            'testPaymentId',
            'testOrderId'
        )->getContent();
        static::assertNotFalse($responseContent);

        $refund = \json_decode($responseContent, true);

        static::assertSame(RefundCaptureResponseFixture::REFUND_AMOUNT, $refund['amount']['total']);
    }

    public function testRefundPaymentWithInvoiceAndAmount(): void
    {
        $request = new Request([], [
            PayPalPaymentController::REQUEST_PARAMETER_REFUND_INVOICE_NUMBER => RefundSaleResponseFixture::TEST_REFUND_INVOICE_NUMBER,
            PayPalPaymentController::REQUEST_PARAMETER_REFUND_AMOUNT => RefundSaleResponseFixture::REFUND_AMOUNT,
            PayPalPaymentController::REQUEST_PARAMETER_CURRENCY => self::TEST_REFUND_CURRENCY,
        ]);
        $refund = $this->refundPayment($request);

        static::assertSame(RefundSaleResponseFixture::REFUND_AMOUNT, $refund['amount']['total']);
        static::assertSame(self::TEST_REFUND_CURRENCY, $refund['amount']['currency']);
        static::assertSame(RefundSaleResponseFixture::TEST_REFUND_INVOICE_NUMBER, $refund['invoice_number']);
    }

    public function testRefundPaymentWithReasonAndDescription(): void
    {
        $request = new Request([], [
            PayPalPaymentController::REQUEST_PARAMETER_REFUND_INVOICE_NUMBER => RefundSaleResponseFixture::TEST_REFUND_INVOICE_NUMBER,
            PayPalPaymentController::REQUEST_PARAMETER_REFUND_AMOUNT => RefundSaleResponseFixture::REFUND_AMOUNT,
            PayPalPaymentController::REQUEST_PARAMETER_CURRENCY => self::TEST_REFUND_CURRENCY,
            PayPalPaymentController::REQUEST_PARAMETER_DESCRIPTION => self::TEST_REFUND_DESCRIPTION,
            PayPalPaymentController::REQUEST_PARAMETER_REASON => self::TEST_REFUND_REASON,
        ]);
        $refund = $this->refundPayment($request);

        static::assertSame(RefundSaleResponseFixture::REFUND_AMOUNT, $refund['amount']['total']);
        static::assertSame(self::TEST_REFUND_CURRENCY, $refund['amount']['currency']);
        static::assertSame(RefundSaleResponseFixture::TEST_REFUND_INVOICE_NUMBER, $refund['invoice_number']);
        static::assertSame(self::TEST_REFUND_REASON, $refund['reason']);
        static::assertSame(self::TEST_REFUND_DESCRIPTION, $refund['description']);
    }

    public function testRefundPaymentWithInvalidResourceType(): void
    {
        $request = new Request();
        $context = Context::createDefaultContext();

        $this->expectException(RequiredParameterInvalidException::class);
        $this->expectExceptionMessage('Required parameter "resourceType" is missing or invalid');
        $this->createPaymentControllerWithSaleResourceMock()->refundPayment(
            $request,
            $context,
            'foo',
            'testPaymentId',
            'testOrderId'
        );
    }

    public function testCapturePaymentAuthorization(): void
    {
        $request = new Request();
        $context = Context::createDefaultContext();

        $responseContent = $this->createPaymentController()->capturePayment(
            $request,
            $context,
            RelatedResource::AUTHORIZE,
            'testPaymentId',
            'testOrderId'
        )->getContent();
        static::assertNotFalse($responseContent);

        $capture = \json_decode($responseContent, true);

        static::assertTrue($capture['is_final_capture']);
    }

    public function testCapturePaymentOrders(): void
    {
        $request = new Request();
        $context = Context::createDefaultContext();

        $responseContent = $this->createPaymentController()->capturePayment(
            $request,
            $context,
            RelatedResource::ORDER,
            'testPaymentId',
            'testOrderId'
        )->getContent();
        static::assertNotFalse($responseContent);

        $capture = \json_decode($responseContent, true);

        static::assertTrue($capture['is_final_capture']);
    }

    public function testCapturePaymentWithInvalidResourceType(): void
    {
        $request = new Request();
        $context = Context::createDefaultContext();

        $this->expectException(RequiredParameterInvalidException::class);
        $this->expectExceptionMessage('Required parameter "resourceType" is missing or invalid');
        $this->createPaymentController()->capturePayment(
            $request,
            $context,
            RelatedResource::SALE,
            'testPaymentId',
            'testOrderId'
        );
    }

    public function testVoidPaymentOrders(): void
    {
        $context = Context::createDefaultContext();
        $responseContent = $this->createPaymentController()->voidPayment(
            $context,
            RelatedResource::ORDER,
            'testResourceId',
            'testOrderId'
        )->getContent();
        static::assertNotFalse($responseContent);

        $void = \json_decode($responseContent, true);

        static::assertSame(VoidOrderResponseFixture::VOID_ID, $void['id']);
    }

    public function testVoidPaymentAuthorize(): void
    {
        $context = Context::createDefaultContext();
        $responseContent = $this->createPaymentController()->voidPayment(
            $context,
            RelatedResource::AUTHORIZE,
            'testResourceId',
            'testOrderId'
        )->getContent();
        static::assertNotFalse($responseContent);

        $void = \json_decode($responseContent, true);

        static::assertSame(VoidAuthorizationResponseFixture::VOID_ID, $void['id']);
    }

    public function testVoidPaymentInvalidResourceType(): void
    {
        $context = Context::createDefaultContext();
        $this->expectException(RequiredParameterInvalidException::class);
        $this->expectExceptionMessage('Required parameter "resourceType" is missing or invalid');
        $this->createPaymentController()->voidPayment(
            $context,
            RelatedResource::SALE,
            'testResourceId',
            'testOrderId'
        );
    }

    private function createPaymentController(): PayPalPaymentController
    {
        return new PayPalPaymentController(
            $this->createPaymentResource(),
            new SaleResource($this->createPayPalClientFactory()),
            new AuthorizationResource($this->createPayPalClientFactory()),
            new OrdersResource($this->createPayPalClientFactory()),
            new CaptureResource($this->createPayPalClientFactory()),
            new PaymentStatusUtilMock(),
            new OrderRepositoryMock()
        );
    }

    private function refundPayment(Request $request): array
    {
        $context = Context::createDefaultContext();
        $responseContent = $this->createPaymentControllerWithSaleResourceMock()->refundPayment(
            $request,
            $context,
            RelatedResource::SALE,
            'testPaymentId',
            'testOrderId'
        )->getContent();
        static::assertNotFalse($responseContent);

        return \json_decode($responseContent, true);
    }

    private function createPaymentControllerWithSaleResourceMock(): PayPalPaymentController
    {
        return new PayPalPaymentController(
            $this->createPaymentResource(),
            new SaleResource($this->createPayPalClientFactory()),
            new AuthorizationResource($this->createPayPalClientFactory()),
            new OrdersResource($this->createPayPalClientFactory()),
            new CaptureResource($this->createPayPalClientFactory()),
            new PaymentStatusUtilMock(),
            new OrderRepositoryMock()
        );
    }
}
