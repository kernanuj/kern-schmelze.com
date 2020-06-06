<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\EventListener;

use KlarnaPayment\Components\Extension\TemplateData\OnsiteMessagingDataExtension;
use KlarnaPayment\Components\OnsiteMessagingReplacer\PlaceholderReplacerInterface;
use KlarnaPayment\Components\Validator\OnsiteMessagingValidator;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OnsiteMessagingEventListener implements EventSubscriberInterface
{
    /** @var SystemConfigService */
    private $systemConfigService;

    /** @var PlaceholderReplacerInterface */
    private $productPriceReplacer;

    /** @var OnsiteMessagingValidator */
    private $onsiteMessagingValidator;

    public function __construct(
        SystemConfigService $systemConfigService,
        PlaceholderReplacerInterface $productPriceReplacer,
        OnsiteMessagingValidator $onsiteMessagingValidator
    ) {
        $this->systemConfigService      = $systemConfigService;
        $this->productPriceReplacer     = $productPriceReplacer;
        $this->onsiteMessagingValidator = $onsiteMessagingValidator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'addKlarnaTemplateData',
        ];
    }

    public function addKlarnaTemplateData(ProductPageLoadedEvent $event): void
    {
        $isActive               = (bool) $this->systemConfigService->get('KlarnaPayment.settings.isOnsiteMessagingActive');
        $onsiteMessagingSnippet = (string) $this->systemConfigService->get('KlarnaPayment.settings.onsiteMessagingSnippet');
        $onsiteMessagingScript  = (string) $this->systemConfigService->get('KlarnaPayment.settings.onsiteMessagingScript');

        if (!$this->onsiteMessagingValidator->isValid($isActive, $onsiteMessagingSnippet, $onsiteMessagingScript)) {
            return;
        }

        $onsiteMessagingSnippet = $this->productPriceReplacer->replace($onsiteMessagingSnippet, $event);

        $temlateData = new OnsiteMessagingDataExtension(
            [
                'klarnaOnsiteMessagingSnippet' => preg_replace("/\r|\n/", '', $onsiteMessagingSnippet),
                'klarnaOnsiteMessagingScript'  => $onsiteMessagingScript,
            ]
        );

        $event->getPage()->addExtension(OnsiteMessagingDataExtension::EXTENSION_NAME, $temlateData);
    }
}
