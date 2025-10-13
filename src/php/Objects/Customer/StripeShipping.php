<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Customer;

use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Stripe\Support\HasMake;

class StripeShipping
{
    use HasMake;

    public function __construct(
        public StripeAddress $address,
        public string        $name,
        public ?string       $phone = null
    ) {}

    public function toArray(): array
    {
        return [
            "address" => $this->address->toArray(),
            "name" => $this->name,
            "phone" => $this->phone,
        ];
    }
}