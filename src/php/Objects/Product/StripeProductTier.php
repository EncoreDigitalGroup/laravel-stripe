<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Product;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\HasMake;

class StripeProductTier
{
    use HasMake;

    public function __construct(
        public int|string|null $upTo = null,
        public ?int $unitAmount = null,
        public ?string $unitAmountDecimal = null,
        public ?int $flatAmount = null,
        public ?string $flatAmountDecimal = null
    ) {}

    public function toArray(): array
    {
        $array = [
            "up_to" => $this->upTo,
            "unit_amount" => $this->unitAmount,
            "unit_amount_decimal" => $this->unitAmountDecimal,
            "flat_amount" => $this->flatAmount,
            "flat_amount_decimal" => $this->flatAmountDecimal,
        ];

        return Arr::whereNotNull($array);
    }
}