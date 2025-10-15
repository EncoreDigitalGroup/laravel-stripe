<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\FinancialConnections;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\HasMake;

class StripeTransactionRefresh
{
    use HasMake;

    // TODO: Change Timestamps from INT to CarbonImmutable
    public function __construct(
        public ?string $id = null,
        public ?int    $lastAttemptedAt = null,
        public ?int    $nextRefreshAvailableAt = null,
        public ?string $status = null
    ) {}

    public function toArray(): array
    {
        $array = [
            'id' => $this->id,
            'last_attempted_at' => $this->lastAttemptedAt,
            'next_refresh_available_at' => $this->nextRefreshAvailableAt,
            'status' => $this->status,
        ];

        return Arr::whereNotNull($array);
    }
}