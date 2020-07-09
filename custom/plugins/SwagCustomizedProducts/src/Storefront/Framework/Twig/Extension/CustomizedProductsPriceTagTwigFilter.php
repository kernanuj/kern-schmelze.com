<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Framework\Twig\Extension;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\Adapter\Twig\Filter\CurrencyFilter;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CustomizedProductsPriceTagTwigFilter extends AbstractExtension
{
    /**
     * @var CurrencyFilter
     */
    private $currencyFilter;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(CurrencyFilter $currencyFilter, TranslatorInterface $translator)
    {
        $this->currencyFilter = $currencyFilter;
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('customized_product_price_tag', [$this, 'generatePriceTag'], ['needs_context' => true]),
        ];
    }

    /**
     * @param OrderLineItemEntity|LineItem $lineItem
     */
    public function generatePriceTag(array $twigContext, $lineItem, string $prefix = '', string $suffix = ''): ?string
    {
        $payload = $lineItem->getPayload();
        $calculatedPrice = $lineItem->getPrice();

        if ($payload === null || $calculatedPrice === null) {
            return null;
        }

        $surcharge = $calculatedPrice->getTotalPrice();

        if ($surcharge === 0.0) {
            return null;
        }

        $sign = $surcharge > 0 ? '+' : '';
        $oneTimeSurchargeText = $this->translator->trans('customizedProducts.priceTagFilter.oneTimeSurchargeSuffix');
        $perItemText = $this->translator->trans('customizedProducts.priceTagFilter.perItemSuffix');
        $star = $this->translator->trans('general.star');

        $displayText = $oneTimeSurchargeText;
        if (!isset($payload['isOneTimeSurcharge']) || !$payload['isOneTimeSurcharge']) {
            $surcharge /= ($lineItem->getQuantity() === 0) ? 1 : $lineItem->getQuantity();
            $displayText = $perItemText;
        }

        return \sprintf(
            '%s(%s%s%s %s)%s',
            $prefix,
            $sign,
            $this->currencyFilter->formatCurrency($twigContext, $surcharge),
            $star,
            $displayText,
            $suffix
        );
    }
}
