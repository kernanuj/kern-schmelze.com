<?php

declare(strict_types=1);

namespace KlarnaPayment\Components\CartHasher;

use LogicException;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CartHasher implements CartHasherInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(Cart $cart, SalesChannelContext $context): string
    {
        $hashData = $this->getHashData($cart, $context);

        return $this->generateHash($hashData);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Cart $cart, string $cartHash, SalesChannelContext $context): bool
    {
        $hashData = $this->getHashData($cart, $context);
        $expected = $this->generateHash($hashData);

        return hash_equals($expected, $cartHash);
    }

    private function getHashData(Cart $cart, SalesChannelContext $context): array
    {
        $hashData = [];

        foreach ($cart->getLineItems() as $item) {
            $detail = [
                'id'       => $item->getReferencedId(),
                'type'     => $item->getType(),
                'quantity' => $item->getQuantity(),
            ];

            if (null !== $item->getPrice()) {
                $detail['price'] = $item->getPrice()->getTotalPrice();
            }

            $hashData[] = $detail;
        }

        $hashData['currency']       = $context->getCurrency()->getId();
        $hashData['paymentMethod']  = $context->getPaymentMethod()->getId();
        $hashData['shippingMethod'] = $context->getShippingMethod()->getId();

        if (null === $context->getCustomer()) {
            return $hashData;
        }

        if (null !== $context->getCustomer()->getActiveBillingAddress()) {
            $hashData['billingAddress'] = $this->hydrateAddress($context->getCustomer()->getActiveBillingAddress());
        }

        if (null !== $context->getCustomer()->getActiveShippingAddress()) {
            $hashData['shippingAddress'] = $this->hydrateAddress($context->getCustomer()->getActiveShippingAddress());
        }

        $hashData['customer'] = [
            'language' => $context->getCustomer()->getLanguageId(),
            'email'    => $context->getCustomer()->getEmail(),
        ];

        if (null !== $context->getCustomer()->getBirthday()) {
            $hashData['birthday'] = $context->getCustomer()->getBirthday()->format(DATE_W3C);
        }

        return $hashData;
    }

    private function generateHash(array $hashData): string
    {
        $json = json_encode($hashData, JSON_PRESERVE_ZERO_FRACTION);

        if (empty($json)) {
            throw new LogicException('could not generate hash');
        }

        $secret = getenv('APP_SECRET');

        if (empty($secret)) {
            throw new LogicException('empty app secret');
        }

        return hash_hmac('sha256', $json, $secret);
    }

    private function hydrateAddress(CustomerAddressEntity $address): array
    {
        return [
            'salutation'      => $address->getSalutationId(),
            'title'           => $address->getTitle(),
            'firstname'       => $address->getFirstName(),
            'lastname'        => $address->getLastName(),
            'street'          => $address->getStreet(),
            'addressaddition' => $address->getAdditionalAddressLine1(),
            'zip'             => $address->getZipcode(),
            'city'            => $address->getCity(),
            'country'         => $address->getCountryId(),
        ];
    }
}
