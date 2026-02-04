<?php

namespace EncoreDigitalGroup\Stripe\Objects\Product;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\RecurringAggregateUsage;
use EncoreDigitalGroup\Stripe\Enums\RecurringInterval;
use EncoreDigitalGroup\Stripe\Enums\RecurringUsageType;
use PHPGenesis\Common\Traits\HasMake;

class StripeRecurring
{
    use HasMake;

    private ?RecurringInterval $interval = null;
    private ?int $intervalCount = null;
    private ?int $trialPeriodDays = null;
    private ?RecurringUsageType $usageType = null;
    private ?RecurringAggregateUsage $aggregateUsage = null;

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

    // Fluent setters
    public function withInterval(RecurringInterval $interval): self
    {
        $this->interval = $interval;

        return $this;
    }

    public function withIntervalCount(int $intervalCount): self
    {
        $this->intervalCount = $intervalCount;

        return $this;
    }

    public function withTrialPeriodDays(int $trialPeriodDays): self
    {
        $this->trialPeriodDays = $trialPeriodDays;

        return $this;
    }

    public function withUsageType(RecurringUsageType $usageType): self
    {
        $this->usageType = $usageType;

        return $this;
    }

    public function withAggregateUsage(RecurringAggregateUsage $aggregateUsage): self
    {
        $this->aggregateUsage = $aggregateUsage;

        return $this;
    }

    // Getters
    public function interval(): ?RecurringInterval
    {
        return $this->interval;
    }

    public function intervalCount(): ?int
    {
        return $this->intervalCount;
    }

    public function trialPeriodDays(): ?int
    {
        return $this->trialPeriodDays;
    }

    public function usageType(): ?RecurringUsageType
    {
        return $this->usageType;
    }

    public function aggregateUsage(): ?RecurringAggregateUsage
    {
        return $this->aggregateUsage;
    }
}