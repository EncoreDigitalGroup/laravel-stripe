<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\FinancialConnections;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use PHPGenesis\Common\Traits\HasMake;

class StripeTransactionRefresh
{
    use HasMake;
    use HasTimestamps;

    private ?string $id = null;
    private ?CarbonImmutable $lastAttemptedAt = null;
    private ?CarbonImmutable $nextRefreshAvailableAt = null;
    private ?string $status = null;

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "last_attempted_at" => self::carbonToTimestamp($this->lastAttemptedAt),
            "next_refresh_available_at" => self::carbonToTimestamp($this->nextRefreshAvailableAt),
            "status" => $this->status,
        ];

        return Arr::whereNotNull($array);
    }

    // Fluent setters
    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withLastAttemptedAt(CarbonImmutable $lastAttemptedAt): self
    {
        $this->lastAttemptedAt = $lastAttemptedAt;

        return $this;
    }

    public function withNextRefreshAvailableAt(CarbonImmutable $nextRefreshAvailableAt): self
    {
        $this->nextRefreshAvailableAt = $nextRefreshAvailableAt;

        return $this;
    }

    public function withStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    // Getter methods
    public function id(): ?string
    {
        return $this->id;
    }

    public function lastAttemptedAt(): ?CarbonImmutable
    {
        return $this->lastAttemptedAt;
    }

    public function nextRefreshAvailableAt(): ?CarbonImmutable
    {
        return $this->nextRefreshAvailableAt;
    }

    public function status(): ?string
    {
        return $this->status;
    }
}