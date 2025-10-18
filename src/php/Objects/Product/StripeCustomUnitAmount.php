<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Product;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use PHPGenesis\Common\Traits\HasMake;

class StripeCustomUnitAmount
{
    use HasMake;

    public function __construct(
        public ?int $minimum = null,
        public ?int $maximum = null,
        public ?int $preset = null
    ) {}

    public function toArray(): array
    {
        $array = [
            "minimum" => $this->minimum,
            "maximum" => $this->maximum,
            "preset" => $this->preset,
        ];

        return Arr::whereNotNull($array);
    }
}