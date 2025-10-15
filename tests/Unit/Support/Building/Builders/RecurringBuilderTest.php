<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Enums\RecurringAggregateUsage;
use EncoreDigitalGroup\Stripe\Enums\RecurringInterval;
use EncoreDigitalGroup\Stripe\Enums\RecurringUsageType;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeRecurring;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\RecurringBuilder;

describe("RecurringBuilder", function (): void {
    test("can build a basic recurring", function (): void {
        $builder = new RecurringBuilder;
        $recurring = $builder->build(
            interval: RecurringInterval::Month
        );

        expect($recurring)
            ->toBeInstanceOf(StripeRecurring::class)
            ->and($recurring->interval)->toBe(RecurringInterval::Month);
    });

    test("can build recurring with all parameters", function (): void {
        $builder = new RecurringBuilder;
        $recurring = $builder->build(
            interval: RecurringInterval::Month,
            intervalCount: 3,
            trialPeriodDays: 14,
            usageType: RecurringUsageType::Metered,
            aggregateUsage: RecurringAggregateUsage::Sum
        );

        expect($recurring)
            ->toBeInstanceOf(StripeRecurring::class)
            ->and($recurring->interval)->toBe(RecurringInterval::Month)
            ->and($recurring->intervalCount)->toBe(3)
            ->and($recurring->trialPeriodDays)->toBe(14)
            ->and($recurring->usageType)->toBe(RecurringUsageType::Metered)
            ->and($recurring->aggregateUsage)->toBe(RecurringAggregateUsage::Sum);
    });

    test("can build weekly recurring", function (): void {
        $builder = new RecurringBuilder;
        $recurring = $builder->build(
            interval: RecurringInterval::Week,
            intervalCount: 2
        );

        expect($recurring)
            ->toBeInstanceOf(StripeRecurring::class)
            ->and($recurring->interval)->toBe(RecurringInterval::Week)
            ->and($recurring->intervalCount)->toBe(2);
    });

    test("can build yearly recurring with trial", function (): void {
        $builder = new RecurringBuilder;
        $recurring = $builder->build(
            interval: RecurringInterval::Year,
            intervalCount: 1,
            trialPeriodDays: 30,
            usageType: RecurringUsageType::Licensed
        );

        expect($recurring)
            ->toBeInstanceOf(StripeRecurring::class)
            ->and($recurring->interval)->toBe(RecurringInterval::Year)
            ->and($recurring->intervalCount)->toBe(1)
            ->and($recurring->trialPeriodDays)->toBe(30)
            ->and($recurring->usageType)->toBe(RecurringUsageType::Licensed)
            ->and($recurring->aggregateUsage)->toBeNull();
    });
});