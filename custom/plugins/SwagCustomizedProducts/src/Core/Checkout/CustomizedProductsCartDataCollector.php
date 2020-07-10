<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Checkout;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Content\Product\Cart\ProductCartProcessor;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsNotAvailableError;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Swag\CustomizedProducts\Storefront\Page\Product\PriceDetail\Route\PriceDetailRoute;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPriceAbleEntity\TemplateOptionPriceAbleEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPriceAware\TemplateOptionPriceAwareInterface;
use Swag\CustomizedProducts\Template\TemplateCollection;
use Swag\CustomizedProducts\Template\TemplateEntity;

class CustomizedProductsCartDataCollector implements CartDataCollectorInterface
{
    public const CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE = 'customized-products';
    public const CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE = 'customized-products-option';
    public const CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE = 'option-values';
    public const CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH = 'customized-products-configuration-hash';
    private const CUSTOMIZED_PRODUCTS_CART_DATA_KEY = 'swag-customized-products-template-';

    /**
     * @var EntityRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var SalesChannelRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        EntityRepositoryInterface $templateRepository,
        SalesChannelRepositoryInterface $productRepository
    ) {
        $this->templateRepository = $templateRepository;
        $this->productRepository = $productRepository;
    }

    public function collect(
        CartDataCollection $data,
        Cart $original,
        SalesChannelContext $salesChannelContext,
        CartBehavior $behavior
    ): void {
        if ($behavior->hasPermission(ProductCartProcessor::SKIP_PRODUCT_RECALCULATION)) {
            return;
        }

        $customizedProductsLineItems = $original->getLineItems()->filterType(
            self::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );

        if ($customizedProductsLineItems->count() === 0) {
            return;
        }

        $templateIds = $customizedProductsLineItems->getReferenceIds();
        if ($templateIds === []) {
            $this->handleError($original, $customizedProductsLineItems, new SwagCustomizedProductsNotAvailableError());

            return;
        }

        $customizedProductsTemplates = $this->fetchTemplates($templateIds, $data, $salesChannelContext);
        foreach ($customizedProductsTemplates as $customizedProductsTemplate) {
            $data->set(
                self::CUSTOMIZED_PRODUCTS_CART_DATA_KEY . $customizedProductsTemplate->getId(),
                $customizedProductsTemplate
            );
        }

        foreach ($customizedProductsLineItems as $customizedProductsLineItem) {
            $lineItemId = $customizedProductsLineItem->getId();

            $productLineItem = $customizedProductsLineItem->getChildren()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE)->first();
            if ($productLineItem === null) {
                $this->handleError($original, $customizedProductsLineItems, new SwagCustomizedProductsNotAvailableError());

                return;
            }

            $customizedProductsLineItem->setDeliveryInformation(
                $productLineItem->getDeliveryInformation()
            );

            $customizedProductsTemplate = $data->get(
                self::CUSTOMIZED_PRODUCTS_CART_DATA_KEY . $customizedProductsLineItem->getReferencedId()
            );

            if (!($customizedProductsTemplate instanceof TemplateEntity)
                || !$this->customizedProductsAvailable($productLineItem, $customizedProductsTemplate, $salesChannelContext)
            ) {
                $this->handleError($original, new LineItemCollection([$customizedProductsLineItem]), new SwagCustomizedProductsNotAvailableError($lineItemId));

                return;
            }

            $templateOptions = $customizedProductsTemplate->getOptions();
            if ($templateOptions === null || $templateOptions->count() <= 0) {
                $this->handleError($original, new LineItemCollection([$customizedProductsLineItem]), new SwagCustomizedProductsNotAvailableError($lineItemId));

                return;
            }

            if (!$original->hasExtension(PriceDetailRoute::PRICE_DETAIL_CALCULATION_EXTENSION_KEY)
                    && !$this->customizedProductsLineItemContainsAllRequiredOptions($customizedProductsLineItem, $templateOptions)
            ) {
                $this->handleError($original, new LineItemCollection([$customizedProductsLineItem]), new SwagCustomizedProductsNotAvailableError($lineItemId));

                return;
            }

            $this->enrichCustomizedProduct(
                $customizedProductsLineItem,
                $customizedProductsTemplate,
                $templateOptions,
                $salesChannelContext,
                $original
            );

            $this->addConfigurationHash($customizedProductsLineItem, $productLineItem, $original);
        }
    }

    /**
     * @param string[] $templateIds
     */
    private function fetchTemplates(
        array $templateIds,
        CartDataCollection $data,
        SalesChannelContext $salesChannelContext
    ): TemplateCollection {
        $filteredIds = [];
        foreach ($templateIds as $templateId) {
            if ($data->has(self::CUSTOMIZED_PRODUCTS_CART_DATA_KEY . $templateId)) {
                continue;
            }

            $filteredIds[] = $templateId;
        }

        if ($filteredIds === []) {
            return new TemplateCollection();
        }

        $criteria = (new Criteria($filteredIds))->addAssociations([
            'options.prices',
            'options.values.prices',
        ]);

        /** @var TemplateCollection $templates */
        $templates = $this->templateRepository->search($criteria, $salesChannelContext->getContext())->getEntities();

        return $templates;
    }

    private function enrichCustomizedProduct(
        LineItem $customizedProductsLineItem,
        TemplateEntity $customizedProductsTemplate,
        TemplateOptionCollection $options,
        SalesChannelContext $salesChannelContext,
        Cart $originalCart
    ): void {
        if ($customizedProductsLineItem->getLabel() === null) {
            $customizedProductsLineItem->setLabel($customizedProductsTemplate->getTranslation('displayName'));
        }

        if (!$customizedProductsLineItem->hasPayloadValue('productNumber')) {
            $customizedProductsLineItem->setPayloadValue('productNumber', '*');
        }

        $optionLineItems = $customizedProductsLineItem->getChildren()->filterType(
            self::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );

        foreach ($optionLineItems as $optionLineItem) {
            if ($optionLineItem->getReferencedId() === null) {
                continue;
            }

            $option = $options->get($optionLineItem->getReferencedId());

            if ($option === null) {
                $this->handleError(
                    $originalCart,
                    new LineItemCollection([$customizedProductsLineItem]),
                    new SwagCustomizedProductsNotAvailableError($optionLineItem->getId())
                );
                continue;
            }

            $optionLineItem->setLabel($option->getTranslation('displayName'));
            $optionLineItem->setPayloadValue('type', $option->getType());
            $optionLineItem->setPayloadValue('isOneTimeSurcharge', $option->isOneTimeSurcharge());

            $itemNumber = $option->getItemNumber();
            $optionLineItem->setPayloadValue('productNumber', '*');
            if ($itemNumber !== null) {
                $optionLineItem->setPayloadValue('productNumber', $itemNumber);
            }

            $fallbackTaxId = $this->getFallbackTaxId($customizedProductsLineItem, $salesChannelContext);

            $optionLineItemPriceDefinition = $this->getPriceDefinition(
                $option,
                $optionLineItem->getQuantity(),
                $salesChannelContext,
                $fallbackTaxId
            );

            if ($optionLineItemPriceDefinition === null) {
                continue;
            }

            $optionLineItem->setPriceDefinition($optionLineItemPriceDefinition);

            $optionValueLineItems = $optionLineItem->getChildren()->filterType(
                self::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
            );

            $optionValues = $option->getValues();

            if ($optionValues === null) {
                return;
            }

            foreach ($optionValueLineItems as $optionValueLineItem) {
                if ($optionValueLineItem->getReferencedId() === null) {
                    continue;
                }

                $optionValue = $optionValues->get($optionValueLineItem->getReferencedId());

                if ($optionValue === null) {
                    continue;
                }

                $displayName = $optionValue->getTranslation('displayName');

                if ($displayName !== null) {
                    $optionValueLineItem->setLabel($displayName);
                }

                $optionValueLineItem->setPayloadValue('value', $optionValue->getValue());
                $optionValueLineItem->setPayloadValue('isOneTimeSurcharge', $optionValue->isOneTimeSurcharge());

                $itemNumber = $optionValue->getItemNumber();
                $optionValueLineItem->setPayloadValue('productNumber', '*');
                if ($itemNumber !== null) {
                    $optionValueLineItem->setPayloadValue('productNumber', $itemNumber);
                }

                $optionValueLineItemPriceDefinition = $this->getPriceDefinition(
                    $optionValue,
                    $optionValueLineItem->getQuantity(),
                    $salesChannelContext,
                    $fallbackTaxId
                );

                if ($optionValueLineItemPriceDefinition === null) {
                    continue;
                }

                $optionValueLineItem->setPriceDefinition($optionValueLineItemPriceDefinition);
            }
        }
    }

    private function getPriceDefinition(
        TemplateOptionPriceAbleEntity $priceAbleEntity,
        int $quantity,
        SalesChannelContext $salesChannelContext,
        ?string $fallbackTaxId
    ): ?PriceDefinitionInterface {
        $isRelative = $priceAbleEntity->isRelativeSurcharge();
        $isAdvanced = $priceAbleEntity->isAdvancedSurcharge();

        if ($isAdvanced) {
            $price = $this->getAdvancedPrice(
                $priceAbleEntity,
                $salesChannelContext,
                $quantity,
                $fallbackTaxId,
                $isRelative
            );
        } else {
            $price = $this->getPrice(
                $priceAbleEntity,
                $salesChannelContext,
                $quantity,
                $fallbackTaxId,
                $isRelative
            );
        }

        return $price;
    }

    private function getPrice(
        TemplateOptionPriceAbleEntity $priceAbleEntity,
        SalesChannelContext $salesChannelContext,
        int $quantity,
        ?string $fallbackTaxId,
        bool $relative
    ): ?PriceDefinitionInterface {
        $currency = $salesChannelContext->getCurrency();

        if (!$relative) {
            $priceAbleEntityTaxId = $priceAbleEntity->getTaxId();

            if ($priceAbleEntityTaxId === null && $fallbackTaxId === null) {
                return null;
            }

            $taxId = $priceAbleEntityTaxId ?? $fallbackTaxId;

            if ($taxId === null || $taxId === '') {
                return null;
            }

            return new QuantityPriceDefinition(
                $this->getQuantityPrice($priceAbleEntity, $currency->getId(), $salesChannelContext),
                $salesChannelContext->buildTaxRules($taxId),
                $currency->getDecimalPrecision(),
                $priceAbleEntity->isOneTimeSurcharge() ? 1 : $quantity,
                true
            );
        }

        $percentageSurcharge = $priceAbleEntity->getPercentageSurcharge();

        if ($percentageSurcharge === null) {
            return null;
        }

        return new PercentagePriceDefinition(
            $percentageSurcharge,
            $currency->getDecimalPrecision()
        );
    }

    private function getAdvancedPrice(
        TemplateOptionPriceAbleEntity $priceAbleEntity,
        SalesChannelContext $salesChannelContext,
        int $quantity,
        ?string $fallbackTaxId,
        bool $relative
    ): ?PriceDefinitionInterface {
        $currency = $salesChannelContext->getCurrency();
        $prices = $priceAbleEntity->getPrices();
        if ($prices === null) {
            return null;
        }

        foreach ($prices as $optionPrice) {
            if (!\in_array($optionPrice->getRuleId(), $salesChannelContext->getRuleIds(), true)) {
                continue;
            }

            if (!$relative) {
                $priceAbleEntityTaxId = $priceAbleEntity->getTaxId();

                if ($priceAbleEntityTaxId === null && $fallbackTaxId === null) {
                    return null;
                }

                $taxId = $priceAbleEntityTaxId ?? $fallbackTaxId;

                if ($taxId === null || $taxId === '') {
                    return null;
                }

                return new QuantityPriceDefinition(
                    $this->getQuantityPrice($optionPrice, $currency->getId(), $salesChannelContext),
                    $salesChannelContext->buildTaxRules($taxId),
                    $currency->getDecimalPrecision(),
                    $priceAbleEntity->isOneTimeSurcharge() ? 1 : $quantity,
                    true
                );
            }

            return new PercentagePriceDefinition(
                $optionPrice->getPercentageSurcharge(),
                $salesChannelContext->getCurrency()->getDecimalPrecision()
            );
        }

        // If no rule matched, we return the standard price definition
        return $this->getPrice($priceAbleEntity, $salesChannelContext, $quantity, $fallbackTaxId, $relative);
    }

    private function getQuantityPrice(
        TemplateOptionPriceAwareInterface $priceAbleEntity,
        string $currencyId,
        SalesChannelContext $context
    ): float {
        $price = $priceAbleEntity->getPrice();

        if ($price === null) {
            return 0.0;
        }

        $currencyPrice = $price->getCurrencyPrice($currencyId);

        if ($currencyPrice === null) {
            return 0.0;
        }

        if ($context->getTaxState() === CartPrice::TAX_STATE_GROSS) {
            $value = $currencyPrice->getGross();
        } else {
            $value = $currencyPrice->getNet();
        }

        if ($currencyPrice->getCurrencyId() === Defaults::CURRENCY) {
            $value *= $context->getContext()->getCurrencyFactor();
        }

        return $value;
    }

    private function getFallbackTaxId(
        LineItem $customizedProductLineItem,
        SalesChannelContext $salesChannelContext
    ): ?string {
        $products = $customizedProductLineItem->getChildren()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE);
        $productLineItem = $products->first();
        if ($productLineItem === null) {
            return null;
        }

        $productReferenceId = $productLineItem->getReferencedId();

        $product = $this->productRepository->search(
            new Criteria([$productReferenceId]),
            $salesChannelContext
        )->get(
            $productReferenceId
        );

        if (!($product instanceof ProductEntity)) {
            return null;
        }

        return $product->getTaxId();
    }

    private function handleError(Cart $original, LineItemCollection $customizedProductsLineItems, Error $error): void
    {
        $original->addErrors($error);

        foreach ($customizedProductsLineItems as $lineItem) {
            $original->remove($lineItem->getId());
        }
    }

    private function customizedProductsAvailable(
        LineItem $productLineItem,
        TemplateEntity $customizedProductsTemplate,
        SalesChannelContext $salesChannelContext
    ): bool {
        $criteria = new Criteria([$productLineItem->getReferencedId()]);
        $criteria->addAssociation(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);

        /** @var ProductEntity|null $product */
        $product = $this->productRepository->search($criteria, $salesChannelContext)->first();

        if ($product === null) {
            return false;
        }

        if (!$product->hasExtension(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN)) {
            return false;
        }

        $template = $product->get(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);

        return $customizedProductsTemplate->isActive()
            && $template instanceof TemplateEntity
            && $template->getId() === $customizedProductsTemplate->getId();
    }

    private function customizedProductsLineItemContainsAllRequiredOptions(
        LineItem $customizedProductsLineItem,
        TemplateOptionCollection $options
    ): bool {
        $requiredOptions = $options->filterByProperty('required', true);
        if ($requiredOptions->count() <= 0) {
            return true;
        }

        $templateOptionsIds = $requiredOptions->getIds();
        $optionLineItems = $customizedProductsLineItem->getChildren()->filterType(
            self::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );

        if ($optionLineItems->count() < $requiredOptions->count()) {
            return false;
        }

        $referencedOptionIds = \array_flip($optionLineItems->getReferenceIds());
        foreach ($templateOptionsIds as $templateOptionsId) {
            if (!\array_key_exists($templateOptionsId, $referencedOptionIds)) {
                return false;
            }
        }

        return true;
    }

    private function addConfigurationHash(LineItem $customizedProductsLineItem, LineItem $productLineItem, Cart $original): void
    {
        $configuration = [];
        $options = $customizedProductsLineItem->getChildren()->filterType(self::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE);
        foreach ($options as $option) {
            $optionReferencedId = $option->getReferencedId();
            if ($optionReferencedId === null) {
                continue;
            }

            $optionValueLineItems = $option->getChildren()->filterType(self::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE);
            if ($optionValueLineItems->count() >= 1) {
                foreach ($optionValueLineItems as $optionValueLineItem) {
                    $configuration[$optionReferencedId][] = $optionValueLineItem->getReferencedId();
                }

                continue;
            }

            if ($option->hasPayloadValue('media')) {
                $configuration[$optionReferencedId] = $option->getPayloadValue('media');

                continue;
            }

            if (!$option->hasPayloadValue('value')) {
                $this->handleError($original, new LineItemCollection([$customizedProductsLineItem]), new SwagCustomizedProductsNotAvailableError($customizedProductsLineItem->getId()));

                continue;
            }

            $configuration[$optionReferencedId] = $option->getPayloadValue('value');
        }

        $arrayToHash = [
            'templateId' => $customizedProductsLineItem->getReferencedId(),
            'productId' => $productLineItem->getReferencedId(),
            'configuration' => $configuration,
        ];

        $customizedProductsLineItem->setPayloadValue(
            self::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH,
            \md5(\serialize($arrayToHash))
        );
    }
}
