<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\EventListener;

use KlarnaPayment\Components\Extension\TemplateData\CheckoutDataExtension;
use KlarnaPayment\Components\Helper\PaymentHelper\PaymentHelperInterface;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutEventListener implements EventSubscriberInterface
{
    /** @var PaymentHelperInterface */
    private $paymentHelper;

    public function __construct(PaymentHelperInterface $paymentHelper)
    {
        $this->paymentHelper = $paymentHelper;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => 'addKlarnaTemplateData',
        ];
    }

    public function addKlarnaTemplateData(CheckoutConfirmPageLoadedEvent $event): void
    {
        if ($this->paymentHelper->isKlarnaPaymentsEnabled($event->getSalesChannelContext())) {
            $type = CheckoutDataExtension::TYPE_PAYMENTS;
        } elseif ($this->paymentHelper->isKlarnaCheckoutEnabled($event->getSalesChannelContext())) {
            $type = CheckoutDataExtension::TYPE_CHECKOUT;
        } else {
            return;
        }

        $templateData = new CheckoutDataExtension();
        $templateData->assign([
            'klarnaType' => $type,
        ]);

        $event->getPage()->addExtension(CheckoutDataExtension::EXTENSION_NAME, $templateData);
    }
}
