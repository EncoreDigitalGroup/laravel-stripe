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
        public ?string $id = null,
        public ?StripeAddress $address = null,
        public ?string $description = null,
        public ?string $email = null,
        public ?string $name = null,
        public ?string $phone = null,
        public ?StripeShipping $shipping = null
    ) {}

    /**
     * Create a StripeCustomer instance from a Stripe API Customer object
     */
    public static function fromStripeObject(Customer $stripeCustomer): self
    {
        $address = null;
        if ($stripeCustomer->address) {
            $address = StripeAddress::make(
                line1: $stripeCustomer->address->line1,
                line2: $stripeCustomer->address->line2,
                city: $stripeCustomer->address->city,
                state: $stripeCustomer->address->state,
                postalCode: $stripeCustomer->address->postal_code,
                country: $stripeCustomer->address->country
            );
        }

        $shipping = null;
        if ($stripeCustomer->shipping) {
            $shippingAddress = StripeAddress::make(
                line1: $stripeCustomer->shipping->address->line1,
                line2: $stripeCustomer->shipping->address->line2,
                city: $stripeCustomer->shipping->address->city,
                state: $stripeCustomer->shipping->address->state,
                postalCode: $stripeCustomer->shipping->address->postal_code,
                country: $stripeCustomer->shipping->address->country
            );

            $shipping = StripeShipping::make(
                address: $shippingAddress,
                name: $stripeCustomer->shipping->name,
                phone: $stripeCustomer->shipping->phone
            );
        }

        return self::make(
            id: $stripeCustomer->id,
            address: $address,
            description: $stripeCustomer->description,
            email: $stripeCustomer->email,
            name: $stripeCustomer->name,
            phone: $stripeCustomer->phone,
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