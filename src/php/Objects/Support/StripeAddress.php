<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Support;

use PHPGenesis\Common\Traits\HasMake;

class StripeAddress
{
    use HasMake;

    public function __construct(
        public ?string $line1 = null,
        public ?string $line2 = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $postalCode = null,
        public ?string $country = null
    ) {}

    public function toArray(): array
    {
        return [
            "line1" => $this->line1,
            "line2" => $this->line2,
            "city" => $this->city,
            "state" => $this->state,
            "postal_code" => $this->postalCode,
            "country" => $this->country,
        ];
    }
}