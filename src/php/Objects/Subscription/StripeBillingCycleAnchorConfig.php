<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Subscription;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\HasMake;

class StripeBillingCycleAnchorConfig
{
    use HasMake;

    public function __construct(
        public ?int $dayOfMonth = null,
        public ?int $month = null,
        public ?int $hour = null,
        public ?int $minute = null,
        public ?int $second = null
    ) {}

    public function toArray(): array
    {
        $array = [
            "day_of_month" => $this->dayOfMonth,
            "month" => $this->month,
            "hour" => $this->hour,
            "minute" => $this->minute,
            "second" => $this->second,
        ];

        return Arr::whereNotNull($array);
    }
}