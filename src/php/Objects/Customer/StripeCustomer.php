<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\Customer;

use EncoreDigitalGroup\Common\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Common\Stripe\Support\HasMake;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use Stripe\Customer;

class StripeCustomer
{
    use HasMake;

    public function __construct(
        public ?string         $id = null,
        public ?StripeAddress  $address = null,
        public ?string         $description = null,
        public ?string         $email = null,
        public ?string         $name = null,
        public ?string         $phone = null,
        public ?StripeShipping $shipping = null
    ) {}

    /**
     * Create a StripeCustomer instance from a Stripe API Customer object
     */
    public static function fromStripeObject(Customer $stripeCustomer): self
    {
        $address = null;
        if (isset($stripeCustomer->address)) {
            /** @var \Stripe\StripeObject $stripeAddress */
            $stripeAddress = $stripeCustomer->address;
            $address = StripeAddress::make(
                line1: $stripeAddress->line1 ?? null,
                line2: $stripeAddress->line2 ?? null,
                city: $stripeAddress->city ?? null,
                state: $stripeAddress->state ?? null,
                postalCode: $stripeAddress->postal_code ?? null,
                country: $stripeAddress->country ?? null
            );
        }

        $shipping = null;
        if (isset($stripeCustomer->shipping)) {
            /** @var \Stripe\StripeObject $stripeShipping */
            $stripeShipping = $stripeCustomer->shipping;
            $shippingAddress = null;
            if (isset($stripeShipping->address)) {
                /** @var \Stripe\StripeObject $shippingAddressObj */
                $shippingAddressObj = $stripeShipping->address;
                $shippingAddress = StripeAddress::make(
                    line1: $shippingAddressObj->line1 ?? null,
                    line2: $shippingAddressObj->line2 ?? null,
                    city: $shippingAddressObj->city ?? null,
                    state: $shippingAddressObj->state ?? null,
                    postalCode: $shippingAddressObj->postal_code ?? null,
                    country: $shippingAddressObj->country ?? null
                );
            }

            // Only create shipping if we have the required fields (address and name)
            if ($shippingAddress !== null && isset($stripeShipping->name)) {
                $shipping = StripeShipping::make(
                    address: $shippingAddress,
                    name: $stripeShipping->name,
                    phone: $stripeShipping->phone ?? null
                );
            }
        }

        return self::make(
            id: $stripeCustomer->id,
            address: $address,
            description: $stripeCustomer->description ?? null,
            email: $stripeCustomer->email,
            name: $stripeCustomer->name ?? null,
            phone: $stripeCustomer->phone ?? null,
            shipping: $shipping
        );
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "address" => $this->address?->toArray(),
            "description" => $this->description,
            "email" => $this->email,
            "name" => $this->name,
            "shipping" => $this->shipping?->toArray(),
        ];

        return Arr::whereNotNull($array);
    }
}