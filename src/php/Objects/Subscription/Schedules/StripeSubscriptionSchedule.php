<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleEndBehavior;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleStatus;
use EncoreDigitalGroup\Stripe\Support\HasTimestamps;
use Illuminate\Support\Collection;
use Stripe\StripeObject;

class StripeSubscriptionSchedule
{
    use HasTimestamps;

    public function __construct(
        public ?string $id = null,
        public ?string $object = null,
        public ?CarbonImmutable $canceledAt = null,
        public ?CarbonImmutable $completedAt = null,
        public ?CarbonImmutable $created = null,
        public ?string $customer = null,
        public ?StripeSubscriptionSchedulePhase $defaultSettings = null,
        public ?SubscriptionScheduleEndBehavior $endBehavior = null,
        public ?bool $livemode = null,
        public ?array $metadata = null,
        public ?Collection $phases = null,
        public ?CarbonImmutable $releasedAt = null,
        public ?string $releasedSubscription = null,
        public ?SubscriptionScheduleStatus $status = null,
        public ?string $subscription = null,
        public ?string $testClock = null,
    ) {}

    public static function make(
        ?string $id = null,
        ?string $object = null,
        ?CarbonImmutable $canceledAt = null,
        ?CarbonImmutable $completedAt = null,
        ?CarbonImmutable $created = null,
        ?string $customer = null,
        ?StripeSubscriptionSchedulePhase $defaultSettings = null,
        ?SubscriptionScheduleEndBehavior $endBehavior = null,
        ?bool $livemode = null,
        ?array $metadata = null,
        ?Collection $phases = null,
        ?CarbonImmutable $releasedAt = null,
        ?string $releasedSubscription = null,
        ?SubscriptionScheduleStatus $status = null,
        ?string $subscription = null,
        ?string $testClock = null,
    ): self {
        return new self(
            id: $id,
            object: $object,
            canceledAt: $canceledAt,
            completedAt: $completedAt,
            created: $created,
            customer: $customer,
            defaultSettings: $defaultSettings,
            endBehavior: $endBehavior,
            livemode: $livemode,
            metadata: $metadata,
            phases: $phases,
            releasedAt: $releasedAt,
            releasedSubscription: $releasedSubscription,
            status: $status,
            subscription: $subscription,
            testClock: $testClock,
        );
    }

    public static function fromStripeObject(StripeObject $obj): self
    {
        $phases = null;
        if (isset($obj->phases)) {
            $phases = collect($obj->phases)->map(function ($phase): \EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedulePhase {
                return StripeSubscriptionSchedulePhase::fromStripeObject($phase);
            });
        }

        $defaultSettings = null;
        if (isset($obj->default_settings)) {
            $defaultSettings = StripeSubscriptionSchedulePhase::fromStripeObject($obj->default_settings);
        }

        $status = null;
        if (isset($obj->status)) {
            $status = SubscriptionScheduleStatus::from($obj->status);
        }

        $endBehavior = null;
        if (isset($obj->end_behavior)) {
            $endBehavior = SubscriptionScheduleEndBehavior::from($obj->end_behavior);
        }

        return self::make(
            id: $obj->id ?? null,
            object: $obj->object ?? null,
            canceledAt: isset($obj->canceled_at) ? self::timestampToCarbon($obj->canceled_at) : null,
            completedAt: isset($obj->completed_at) ? self::timestampToCarbon($obj->completed_at) : null,
            created: isset($obj->created) ? self::timestampToCarbon($obj->created) : null,
            customer: is_string($obj->customer ?? null) ? $obj->customer : $obj->customer?->id,
            defaultSettings: $defaultSettings,
            endBehavior: $endBehavior,
            livemode: $obj->livemode ?? null,
            metadata: isset($obj->metadata) ? $obj->metadata->toArray() : null,
            phases: $phases,
            releasedAt: isset($obj->released_at) ? self::timestampToCarbon($obj->released_at) : null,
            releasedSubscription: is_string($obj->released_subscription ?? null) ? $obj->released_subscription : $obj->released_subscription?->id,
            status: $status,
            subscription: is_string($obj->subscription ?? null) ? $obj->subscription : $obj->subscription?->id,
            testClock: $obj->test_clock ?? null,
        );
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "object" => $this->object,
            "canceled_at" => self::carbonToTimestamp($this->canceledAt),
            "completed_at" => self::carbonToTimestamp($this->completedAt),
            "created" => self::carbonToTimestamp($this->created),
            "customer" => $this->customer,
            "default_settings" => $this->defaultSettings?->toArray(),
            "end_behavior" => $this->endBehavior?->value,
            "livemode" => $this->livemode,
            "metadata" => $this->metadata,
            "phases" => $this->phases?->map(fn($phase) => $phase->toArray())->toArray(),
            "released_at" => self::carbonToTimestamp($this->releasedAt),
            "released_subscription" => $this->releasedSubscription,
            "status" => $this->status?->value,
            "subscription" => $this->subscription,
            "test_clock" => $this->testClock,
        ];

        return Arr::whereNotNull($array);
    }
}