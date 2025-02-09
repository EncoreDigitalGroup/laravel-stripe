<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\Customer;

use EncoreDigitalGroup\Common\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Common\Stripe\Support\HasMake;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;

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
    )
    {
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