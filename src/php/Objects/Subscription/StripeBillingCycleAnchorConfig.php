<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Subscription;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use PHPGenesis\Common\Traits\HasMake;

class StripeBillingCycleAnchorConfig
{
    use HasMake;

    private ?int $dayOfMonth = null;
    private ?int $month = null;
    private ?int $hour = null;
    private ?int $minute = null;
    private ?int $second = null;

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

    // Fluent setters
    public function withDayOfMonth(int $dayOfMonth): self
    {
        $this->dayOfMonth = $dayOfMonth;

        return $this;
    }

    public function withMonth(int $month): self
    {
        $this->month = $month;

        return $this;
    }

    public function withHour(int $hour): self
    {
        $this->hour = $hour;

        return $this;
    }

    public function withMinute(int $minute): self
    {
        $this->minute = $minute;

        return $this;
    }

    public function withSecond(int $second): self
    {
        $this->second = $second;

        return $this;
    }

    public function dayOfMonth(): ?int
    {
        return $this->dayOfMonth;
    }

    public function month(): ?int
    {
        return $this->month;
    }

    public function hour(): ?int
    {
        return $this->hour;
    }

    public function minute(): ?int
    {
        return $this->minute;
    }

    public function second(): ?int
    {
        return $this->second;
    }
}