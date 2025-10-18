<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Product;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\RecurringAggregateUsage;
use EncoreDigitalGroup\Stripe\Enums\RecurringInterval;
use EncoreDigitalGroup\Stripe\Enums\RecurringUsageType;
use PHPGenesis\Common\Traits\HasMake;

class StripeRecurring
{
    use HasMake;

    public function __construct(
        public ?RecurringInterval $interval = null,
        public ?int $intervalCount = null,
        public ?int $trialPeriodDays = null,
        public ?RecurringUsageType $usageType = null,
        public ?RecurringAggregateUsage $aggregateUsage = null
    ) {}

    public function toArray(): array
    {
        $array = [
            "interval" => $this->interval?->value,
            "interval_count" => $this->intervalCount,
            "trial_period_days" => $this->trialPeriodDays,
            "usage_type" => $this->usageType?->value,
            "aggregate_usage" => $this->aggregateUsage?->value,
        ];

        return Arr::whereNotNull($array);
    }
}