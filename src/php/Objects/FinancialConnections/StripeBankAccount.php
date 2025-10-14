<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\FinancialConnections;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\HasMake;

class StripeBankAccount
{
    use HasMake;

    public function __construct(
        public ?string $id = null,
        public ?string $category = null,
        public ?int $created = null,
        public ?string $displayName = null,
        public ?string $institutionName = null,
        public ?string $last4 = null,
        public ?bool $liveMode = null,
        public array $permissions = [],
        public array $subscriptions = [],
        public array $supportedPaymentMethodTypes = [],
        public ?StripeTransactionRefresh $transactionRefresh = null
    ) {}

    public function toArray(): array
    {
        $array = [
            'id' => $this->id,
            'category' => $this->category,
            'created' => $this->created,
            'display_name' => $this->displayName,
            'institution_name' => $this->institutionName,
            'last4' => $this->last4,
            'live_mode' => $this->liveMode,
            'permissions' => $this->permissions,
            'subscriptions' => $this->subscriptions,
            'supported_payment_method_types' => $this->supportedPaymentMethodTypes,
            'transaction_refresh' => $this->transactionRefresh?->toArray(),
        ];

        return Arr::whereNotNull($array);
    }
}