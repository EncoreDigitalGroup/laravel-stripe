<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace Tests\Integration;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleEndBehavior;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleProrationBehavior;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleStatus;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedule;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedulePhase;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\SubscriptionScheduleBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\SubscriptionSchedulePhaseBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\StripeBuilder;

describe("StripeBuilder subscription schedule methods", function (): void {
    test("provides subscription schedule builder", function (): void {
        $builder = new StripeBuilder();

        $subscriptionScheduleBuilder = $builder->subscriptionSchedule();

        expect($subscriptionScheduleBuilder)
            ->toBeInstanceOf(SubscriptionScheduleBuilder::class);
    });

    test("provides subscription schedule phase builder", function (): void {
        $builder = new StripeBuilder();

        $phaseBuilder = $builder->subscriptionSchedulePhase();

        expect($phaseBuilder)
            ->toBeInstanceOf(SubscriptionSchedulePhaseBuilder::class);
    });
});

describe("SubscriptionScheduleBuilder", function (): void {
    test("builds subscription schedule with all parameters", function (): void {
        $builder = new StripeBuilder();
        $now = CarbonImmutable::now();
        $phases = collect([
            $builder->subscriptionSchedulePhase()->build(
                startDate: $now,
                endDate: $now->addMonth(),
            ),
        ]);

        $schedule = $builder->subscriptionSchedule()->build(
            id: "sub_sched_test123",
            customer: "cus_test123",
            endBehavior: SubscriptionScheduleEndBehavior::Release,
            metadata: ["key" => "value"],
            phases: $phases,
            status: SubscriptionScheduleStatus::Active,
        );

        expect($schedule)
            ->toBeInstanceOf(StripeSubscriptionSchedule::class)
            ->and($schedule->id)->toBe("sub_sched_test123")
            ->and($schedule->customer)->toBe("cus_test123")
            ->and($schedule->endBehavior)->toBe(SubscriptionScheduleEndBehavior::Release)
            ->and($schedule->status)->toBe(SubscriptionScheduleStatus::Active)
            ->and($schedule->phases)->toBe($phases)
            ->and($schedule->metadata)->toBe(["key" => "value"]);
    });

    test("builds subscription schedule with minimal parameters", function (): void {
        $builder = new StripeBuilder();

        $schedule = $builder->subscriptionSchedule()->build();

        expect($schedule)
            ->toBeInstanceOf(StripeSubscriptionSchedule::class)
            ->and($schedule->id)->toBeNull()
            ->and($schedule->customer)->toBeNull()
            ->and($schedule->endBehavior)->toBeNull()
            ->and($schedule->status)->toBeNull()
            ->and($schedule->phases)->toBeNull();
    });
});

describe("SubscriptionSchedulePhaseBuilder", function (): void {
    test("builds subscription schedule phase with all parameters", function (): void {
        $builder = new StripeBuilder();
        $now = CarbonImmutable::now();
        $endDate = $now->addMonth();
        $trialEnd = $now->addDays(14);
        $items = collect([
            ["price" => "price_test123", "quantity" => 1],
        ]);
        $taxRates = collect(["txr_test123"]);

        $phase = $builder->subscriptionSchedulePhase()->build(
            startDate: $now,
            endDate: $endDate,
            items: $items,
            iterations: 3,
            prorationBehavior: SubscriptionScheduleProrationBehavior::None,
            trialPeriodDays: 14,
            trialEnd: $trialEnd,
            defaultPaymentMethod: "pm_test123",
            defaultTaxRates: $taxRates,
            collectionMethod: "charge_automatically",
            metadata: ["key" => "value"],
        );

        expect($phase)
            ->toBeInstanceOf(StripeSubscriptionSchedulePhase::class)
            ->and($phase->startDate)->toBe($now)
            ->and($phase->endDate)->toBe($endDate)
            ->and($phase->items)->toBe($items)
            ->and($phase->iterations)->toBe(3)
            ->and($phase->prorationBehavior)->toBe(SubscriptionScheduleProrationBehavior::None)
            ->and($phase->trialPeriodDays)->toBe(14)
            ->and($phase->trialEnd)->toBe($trialEnd)
            ->and($phase->defaultPaymentMethod)->toBe("pm_test123")
            ->and($phase->defaultTaxRates)->toBe($taxRates)
            ->and($phase->collectionMethod)->toBe("charge_automatically")
            ->and($phase->metadata)->toBe(["key" => "value"]);
    });

    test("builds subscription schedule phase with minimal parameters", function (): void {
        $builder = new StripeBuilder();

        $phase = $builder->subscriptionSchedulePhase()->build();

        expect($phase)
            ->toBeInstanceOf(StripeSubscriptionSchedulePhase::class)
            ->and($phase->startDate)->toBeNull()
            ->and($phase->endDate)->toBeNull()
            ->and($phase->items)->toBeNull()
            ->and($phase->iterations)->toBeNull()
            ->and($phase->prorationBehavior)->toBeNull();
    });
});

describe("Stripe facade integration", function (): void {
    test("provides builder through facade", function (): void {
        $builder = Stripe::builder();

        $subscriptionScheduleBuilder = $builder->subscriptionSchedule();
        $phaseBuilder = $builder->subscriptionSchedulePhase();

        expect($subscriptionScheduleBuilder)
            ->toBeInstanceOf(SubscriptionScheduleBuilder::class)
            ->and($phaseBuilder)
            ->toBeInstanceOf(SubscriptionSchedulePhaseBuilder::class);
    });

    test("creates complex nested schedule through builders", function (): void {
        $builder = Stripe::builder();
        $now = CarbonImmutable::now();
        $phase1EndDate = $now->addMonth();
        $phase2StartDate = $now->addMonth();
        $phase2EndDate = $now->addMonths(2);

        $phase1 = $builder->subscriptionSchedulePhase()->build(
            startDate: $now,
            endDate: $phase1EndDate,
            items: collect([
                ["price" => "price_trial", "quantity" => 1],
            ]),
            prorationBehavior: SubscriptionScheduleProrationBehavior::None,
        );

        $phase2 = $builder->subscriptionSchedulePhase()->build(
            startDate: $phase2StartDate,
            endDate: $phase2EndDate,
            items: collect([
                ["price" => "price_regular", "quantity" => 1],
            ]),
            prorationBehavior: SubscriptionScheduleProrationBehavior::CreateProrations,
        );

        $schedule = $builder->subscriptionSchedule()->build(
            customer: "cus_test123",
            endBehavior: SubscriptionScheduleEndBehavior::Release,
            metadata: ["created_by" => "test"],
            phases: collect([$phase1, $phase2]),
        );

        expect($schedule)
            ->toBeInstanceOf(StripeSubscriptionSchedule::class)
            ->and($schedule->customer)->toBe("cus_test123")
            ->and($schedule->endBehavior)->toBe(SubscriptionScheduleEndBehavior::Release)
            ->and($schedule->phases)->toHaveCount(2)
            ->and($schedule->metadata)->toBe(["created_by" => "test"])
            ->and($schedule->phases->first())
            ->toBeInstanceOf(StripeSubscriptionSchedulePhase::class)
            ->and($schedule->phases->first()->prorationBehavior)->toBe(SubscriptionScheduleProrationBehavior::None)
            ->and($schedule->phases->last()->prorationBehavior)->toBe(SubscriptionScheduleProrationBehavior::CreateProrations);

    });
});