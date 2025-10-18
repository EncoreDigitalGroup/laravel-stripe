<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\FinancialConnections;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\HasTimestamps;
use PHPGenesis\Common\Traits\HasMake;

class StripeTransactionRefresh
{
    use HasMake;
    use HasTimestamps;

    public function __construct(
        public ?string $id = null,
        public ?CarbonImmutable $lastAttemptedAt = null,
        public ?CarbonImmutable $nextRefreshAvailableAt = null,
        public ?string $status = null
    ) {}

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
}