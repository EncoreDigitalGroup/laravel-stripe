<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\HasMake;

class StripePhaseItem
{
    use HasMake;

    public function __construct(
        public string $price,
        public int $quantity = 1,
        public ?array $metadata = null,
    ) {}

    public function toArray(): array
    {
        return Arr::whereNotNull([
            "price" => $this->price,
            "quantity" => $this->quantity,
            "metadata" => $this->metadata,
        ]);
    }
}