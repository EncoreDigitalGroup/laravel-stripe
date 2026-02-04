<?php



use EncoreDigitalGroup\Stripe\Enums\RecurringAggregateUsage;
use EncoreDigitalGroup\Stripe\Enums\RecurringInterval;
use EncoreDigitalGroup\Stripe\Enums\RecurringUsageType;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeRecurring;

describe("StripeRecurring", function (): void {
    test("can create StripeRecurring using make method", function (): void {
        $recurring = StripeRecurring::make(
            interval: RecurringInterval::Month,
            intervalCount: 1,
            trialPeriodDays: 14,
            usageType: RecurringUsageType::Licensed,
            aggregateUsage: RecurringAggregateUsage::Sum
        );

        expect($recurring)
            ->toBeInstanceOf(StripeRecurring::class)
            ->and($recurring->interval())->toBe(RecurringInterval::Month)
            ->and($recurring->intervalCount())->toBe(1)
            ->and($recurring->trialPeriodDays())->toBe(14)
            ->and($recurring->usageType())->toBe(RecurringUsageType::Licensed)
            ->and($recurring->aggregateUsage())->toBe(RecurringAggregateUsage::Sum);
    });

    test("can create StripeRecurring with minimal parameters", function (): void {
        $recurring = StripeRecurring::make(
            interval: RecurringInterval::Year
        );

        expect($recurring)
            ->toBeInstanceOf(StripeRecurring::class)
            ->and($recurring->interval())->toBe(RecurringInterval::Year)
            ->and($recurring->intervalCount())->toBeNull()
            ->and($recurring->trialPeriodDays())->toBeNull()
            ->and($recurring->usageType())->toBeNull()
            ->and($recurring->aggregateUsage())->toBeNull();
    });

    test("can create StripeRecurring for metered billing", function (): void {
        $recurring = StripeRecurring::make(
            interval: RecurringInterval::Month,
            intervalCount: 1,
            usageType: RecurringUsageType::Metered,
            aggregateUsage: RecurringAggregateUsage::Max
        );

        expect($recurring)
            ->toBeInstanceOf(StripeRecurring::class)
            ->and($recurring->interval())->toBe(RecurringInterval::Month)
            ->and($recurring->intervalCount())->toBe(1)
            ->and($recurring->usageType())->toBe(RecurringUsageType::Metered)
            ->and($recurring->aggregateUsage())->toBe(RecurringAggregateUsage::Max)
            ->and($recurring->trialPeriodDays())->toBeNull();
    });

    test("toArray returns correct structure", function (): void {
        $recurring = StripeRecurring::make(
            interval: RecurringInterval::Week,
            intervalCount: 2,
            trialPeriodDays: 7,
            usageType: RecurringUsageType::Licensed
        );

        $array = $recurring->toArray();

        expect($array)
            ->toBeArray()
            ->and($array["interval"])->toBe("week")
            ->and($array["interval_count"])->toBe(2)
            ->and($array["trial_period_days"])->toBe(7)
            ->and($array["usage_type"])->toBe("licensed");
    });

    test("toArray filters null values", function (): void {
        $recurring = StripeRecurring::make(
            interval: RecurringInterval::Month
        );

        $array = $recurring->toArray();

        expect($array)
            ->toBeArray()
            ->and($array)->toHaveKey("interval")
            ->and($array["interval"])->toBe("month")
            ->and($array)->not->toHaveKey("interval_count")
            ->and($array)->not->toHaveKey("trial_period_days")
            ->and($array)->not->toHaveKey("usage_type")
            ->and($array)->not->toHaveKey("aggregate_usage");
    });

    test("toArray converts enums to values", function (): void {
        $recurring = StripeRecurring::make(
            interval: RecurringInterval::Day,
            usageType: RecurringUsageType::Metered,
            aggregateUsage: RecurringAggregateUsage::LastDuringPeriod
        );

        $array = $recurring->toArray();

        expect($array)
            ->toBeArray()
            ->and($array["interval"])->toBe("day")
            ->and($array["usage_type"])->toBe("metered")
            ->and($array["aggregate_usage"])->toBe("last_during_period");
    });
});