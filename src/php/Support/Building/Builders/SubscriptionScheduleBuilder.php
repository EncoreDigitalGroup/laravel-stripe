<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleEndBehavior;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleStatus;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedule;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedulePhase;
use Illuminate\Support\Collection;

class SubscriptionScheduleBuilder
{
    public function build(
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
    ): StripeSubscriptionSchedule {
        return StripeSubscriptionSchedule::make(
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
}