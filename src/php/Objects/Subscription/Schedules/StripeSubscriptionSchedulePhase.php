<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleProrationBehavior;
use EncoreDigitalGroup\Stripe\Support\HasTimestamps;
use Illuminate\Support\Collection;
use Stripe\StripeObject;

class StripeSubscriptionSchedulePhase
{
    use HasTimestamps;

    public function __construct(
        public ?CarbonImmutable $startDate = null,
        public ?CarbonImmutable $endDate = null,
        public ?Collection $items = null,
        public ?int $iterations = null,
        public ?SubscriptionScheduleProrationBehavior $prorationBehavior = null,
        public ?int $trialPeriodDays = null,
        public ?CarbonImmutable $trialEnd = null,
        public ?string $defaultPaymentMethod = null,
        public ?Collection $defaultTaxRates = null,
        public ?string $collectionMethod = null,
        public ?string $invoiceSettings = null,
        public ?array $metadata = null,
    ) {}

    public static function make(
        ?CarbonImmutable $startDate = null,
        ?CarbonImmutable $endDate = null,
        ?Collection $items = null,
        ?int $iterations = null,
        ?SubscriptionScheduleProrationBehavior $prorationBehavior = null,
        ?int $trialPeriodDays = null,
        ?CarbonImmutable $trialEnd = null,
        ?string $defaultPaymentMethod = null,
        ?Collection $defaultTaxRates = null,
        ?string $collectionMethod = null,
        ?string $invoiceSettings = null,
        ?array $metadata = null,
    ): self {
        return new self(
            startDate: $startDate,
            endDate: $endDate,
            items: $items,
            iterations: $iterations,
            prorationBehavior: $prorationBehavior,
            trialPeriodDays: $trialPeriodDays,
            trialEnd: $trialEnd,
            defaultPaymentMethod: $defaultPaymentMethod,
            defaultTaxRates: $defaultTaxRates,
            collectionMethod: $collectionMethod,
            invoiceSettings: $invoiceSettings,
            metadata: $metadata,
        );
    }

    public static function fromStripeObject(StripeObject $obj): self
    {
        $items = null;
        if (isset($obj->items->data)) {
            $items = collect($obj->items->data)->map(function ($item) {
                return [
                    'price' => $item->price ?? null,
                    'quantity' => $item->quantity ?? null,
                    'metadata' => isset($item->metadata) ? $item->metadata->toArray() : null,
                ];
            });
        }

        $defaultTaxRates = null;
        if (isset($obj->default_tax_rates)) {
            $defaultTaxRates = collect($obj->default_tax_rates);
        }

        $prorationBehavior = null;
        if (isset($obj->proration_behavior)) {
            $prorationBehavior = SubscriptionScheduleProrationBehavior::from($obj->proration_behavior);
        }

        return self::make(
            startDate: isset($obj->start_date) ? self::timestampToCarbon($obj->start_date) : null,
            endDate: isset($obj->end_date) ? self::timestampToCarbon($obj->end_date) : null,
            items: $items,
            iterations: $obj->iterations ?? null,
            prorationBehavior: $prorationBehavior,
            trialPeriodDays: $obj->trial_period_days ?? null,
            trialEnd: isset($obj->trial_end) ? self::timestampToCarbon($obj->trial_end) : null,
            defaultPaymentMethod: $obj->default_payment_method ?? null,
            defaultTaxRates: $defaultTaxRates,
            collectionMethod: $obj->collection_method ?? null,
            invoiceSettings: $obj->invoice_settings ?? null,
            metadata: isset($obj->metadata) ? $obj->metadata->toArray() : null,
        );
    }

    public function toArray(): array
    {
        $array = [
            'start_date' => self::carbonToTimestamp($this->startDate),
            'end_date' => self::carbonToTimestamp($this->endDate),
            'items' => $this->items?->toArray(),
            'iterations' => $this->iterations,
            'proration_behavior' => $this->prorationBehavior?->value,
            'trial_period_days' => $this->trialPeriodDays,
            'trial_end' => self::carbonToTimestamp($this->trialEnd),
            'default_payment_method' => $this->defaultPaymentMethod,
            'default_tax_rates' => $this->defaultTaxRates?->toArray(),
            'collection_method' => $this->collectionMethod,
            'invoice_settings' => $this->invoiceSettings,
            'metadata' => $this->metadata,
        ];

        return Arr::whereNotNull($array);
    }
}