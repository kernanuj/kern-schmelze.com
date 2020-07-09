<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\Client\Hydrator\Struct\ShippingOptions;

use KlarnaPayment\Components\Client\Struct\ShippingOption;
use KlarnaPayment\Components\InstantShopping\ContextHandler\ContextHandlerInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Shipping\ShippingMethodCollection;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ShippingOptionsStructHydrator implements ShippingOptionsStructHydratorInterface
{
    /** @var SalesChannelRepositoryInterface */
    private $shippingMethodRepository;

    /** @var CartService */
    private $cartService;

    /** @var ContextHandlerInterface */
    private $contextHandler;

    public function __construct(SalesChannelRepositoryInterface $shippingMethodRepository, CartService $cartService, ContextHandlerInterface $contextHandler)
    {
        $this->shippingMethodRepository = $shippingMethodRepository;
        $this->cartService              = $cartService;
        $this->contextHandler           = $contextHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(Cart $cart, SalesChannelContext $context): array
    {
        $shippingOptions = [];

        $precision               = $context->getCurrency()->getDecimalPrecision();
        $currentShippingMethodId = $context->getShippingMethod()->getId();
        $customer                = $context->getCustomer();

        /** @var ShippingMethodEntity $shippingMethod */
        foreach ($this->getShippingMethods($context) as $shippingMethod) {
            $tax = new CalculatedTax(0, 0, 0);

            $context = $this->contextHandler->createSalesChannelContext(
                $context->getToken(),
                $customer !== null ? $customer->getId() : null,
                $context->getCurrency()->getId(),
                $shippingMethod->getId(),
                $context
            );
            $cart = $this->cartService->recalculate($cart, $context);

            // TODO: Only one delivery is supported currently, this could change in the future
            $delivery = $cart->getDeliveries()->first();

            if (!$delivery) {
                continue;
            }

            if ($delivery->getShippingCosts()->getCalculatedTaxes()->count() === 1) {
                /** @var CalculatedTax $tax */
                $tax = $delivery->getShippingCosts()->getCalculatedTaxes()->first();
            }

            if ($delivery->getShippingCosts()->getCalculatedTaxes()->count() > 1) {
                $tax = $this->getCalculatedMixedTaxes($delivery->getShippingCosts()->getCalculatedTaxes(), $delivery->getShippingCosts()->getTotalPrice());
            }

            $deliveryShippingMethod = $delivery->getShippingMethod();
            $option                 = new ShippingOption();
            $option->assign([
                'id'          => $deliveryShippingMethod->getId(),
                'name'        => $deliveryShippingMethod->getName(),
                'description' => (string) $deliveryShippingMethod->getDescription(),
                'precision'   => $precision,
                'price'       => $delivery->getShippingCosts()->getTotalPrice(),
                'taxAmount'   => $tax->getTax(),
                'taxRate'     => $tax->getTaxRate() * 100,
            ]);

            if ($currentShippingMethodId === $deliveryShippingMethod->getId()) {
                array_unshift($shippingOptions, $option);
            } else {
                $shippingOptions[] = $option;
            }
        }

        // Reset context
        $context = $this->contextHandler->createSalesChannelContext(
            $context->getToken(),
            $customer !== null ? $customer->getId() : null,
            $context->getCurrency()->getId(),
            $currentShippingMethodId,
            $context
        );
        $cart = $this->cartService->recalculate($cart, $context);

        return $shippingOptions;
    }

    private function getCalculatedMixedTaxes(CalculatedTaxCollection $taxes, float $totalPrice): CalculatedTax
    {
        $taxAmount = 0;

        if ($totalPrice <= 0.01) {
            return new CalculatedTax(0, 0, $totalPrice);
        }

        foreach ($taxes as $value) {
            $taxAmount += $value->getTax();
        }

        $taxRate = (($totalPrice / ($totalPrice - $taxAmount)) - 1) * 100;

        return new CalculatedTax($taxAmount, $taxRate, $totalPrice);
    }

    private function getShippingMethods(SalesChannelContext $salesChannelContext): ShippingMethodCollection
    {
        $criteria = (new Criteria())->addFilter(new EqualsFilter('active', true));

        /** @var ShippingMethodCollection $shippingMethods */
        $shippingMethods = $this->shippingMethodRepository->search($criteria, $salesChannelContext)->getEntities();

        return $shippingMethods->filterByActiveRules($salesChannelContext);
    }
}
