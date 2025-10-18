<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleProrationBehavior;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedulePhase;
use Stripe\Util\Util;

test("can create StripeSubscriptionSchedulePhase using make method", function (): void {
    $now = CarbonImmutable::now();
    $endDate = $now->addMonth();
    $trialEnd = $now->addDays(14);
    $items = collect([
        ["price" => "price_test123", "quantity" => 1],
        ["price" => "price_test456", "quantity" => 2],
    ]);
    $taxRates = collect(["txr_test123", "txr_test456"]);

    $phase = StripeSubscriptionSchedulePhase::make()
        ->withStartDate($now)
        ->withEndDate($endDate)
        ->withItems($items)
        ->withIterations(3)
        ->withProrationBehavior(SubscriptionScheduleProrationBehavior::None)
        ->withTrialPeriodDays(14)
        ->withTrialEnd($trialEnd)
        ->withDefaultPaymentMethod("pm_test123")
        ->withDefaultTaxRates($taxRates)
        ->withCollectionMethod("charge_automatically")
        ->withMetadata(["key" => "value"]);

    expect($phase->startDate())->toBe($now)
        ->and($phase->endDate())->toBe($endDate)
        ->and($phase->items())->toBe($items)
        ->and($phase->iterations())->toBe(3)
        ->and($phase->prorationBehavior())->toBe(SubscriptionScheduleProrationBehavior::None)
        ->and($phase->trialPeriodDays())->toBe(14)
        ->and($phase->trialEnd())->toBe($trialEnd)
        ->and($phase->defaultPaymentMethod())->toBe("pm_test123")
        ->and($phase->defaultTaxRates())->toBe($taxRates)
        ->and($phase->collectionMethod())->toBe("charge_automatically")
        ->and($phase->metadata())->toBe(["key" => "value"]);
});

test("can create StripeSubscriptionSchedulePhase with nullable parameters", function (): void {
    $phase = StripeSubscriptionSchedulePhase::make();

    expect($phase->startDate())->toBeNull()
        ->and($phase->endDate())->toBeNull()
        ->and($phase->items())->toBeNull()
        ->and($phase->iterations())->toBeNull()
        ->and($phase->prorationBehavior())->toBeNull()
        ->and($phase->trialPeriodDays())->toBeNull()
        ->and($phase->trialEnd())->toBeNull()
        ->and($phase->defaultPaymentMethod())->toBeNull()
        ->and($phase->defaultTaxRates())->toBeNull()
        ->and($phase->collectionMethod())->toBeNull()
        ->and($phase->metadata())->toBeNull();
});

test("can convert from Stripe object with all fields", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "start_date" => 1640995200, // 2022-01-01 00:00:00 UTC
        "end_date" => 1643673600,   // 2022-02-01 00:00:00 UTC
        "iterations" => 3,
        "proration_behavior" => "always_invoice",
        "trial_period_days" => 7,
        "trial_end" => 1641600000,  // 2022-01-08 00:00:00 UTC
        "default_payment_method" => "pm_test123",
        "collection_method" => "send_invoice",
        "invoice_settings" => "some_settings",
        "metadata" => ["phase" => "test"],
        "items" => [
            "data" => [
                [
                    "price" => "price_test123",
                    "quantity" => 2,
                    "metadata" => ["item" => "test"],
                ],
            ],
        ],
        "default_tax_rates" => ["txr_test123", "txr_test456"],
    ], []);

    $phase = StripeSubscriptionSchedulePhase::fromStripeObject($stripeObject);

    expect($phase->startDate())->toBeInstanceOf(CarbonImmutable::class)
        ->and($phase->startDate()->timestamp)->toBe(1640995200)
        ->and($phase->endDate())->toBeInstanceOf(CarbonImmutable::class)
        ->and($phase->endDate()->timestamp)->toBe(1643673600)
        ->and($phase->iterations())->toBe(3)
        ->and($phase->prorationBehavior())->toBe(SubscriptionScheduleProrationBehavior::AlwaysInvoice)
        ->and($phase->trialPeriodDays())->toBe(7)
        ->and($phase->trialEnd())->toBeInstanceOf(CarbonImmutable::class)
        ->and($phase->trialEnd()->timestamp)->toBe(1641600000)
        ->and($phase->defaultPaymentMethod())->toBe("pm_test123")
        ->and($phase->collectionMethod())->toBe("send_invoice")
        ->and($phase->invoiceSettings())->toBe("some_settings")
        ->and($phase->metadata())->toBe(["phase" => "test"]);

    expect($phase->items())->toHaveCount(1)
        ->and($phase->items()->first()["price"])->toBe("price_test123")
        ->and($phase->items()->first()["quantity"])->toBe(2)
        ->and($phase->items()->first()["metadata"])->toBe(["item" => "test"]);

    expect($phase->defaultTaxRates())->toHaveCount(2)
        ->and($phase->defaultTaxRates()->first())->toBe("txr_test123")
        ->and($phase->defaultTaxRates()->last())->toBe("txr_test456");
});

test("handles missing optional fields in fromStripeObject", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "start_date" => 1640995200,
    ], []);

    $phase = StripeSubscriptionSchedulePhase::fromStripeObject($stripeObject);

    expect($phase->startDate())->toBeInstanceOf(CarbonImmutable::class)
        ->and($phase->endDate())->toBeNull()
        ->and($phase->items())->toBeNull()
        ->and($phase->iterations())->toBeNull()
        ->and($phase->prorationBehavior())->toBeNull()
        ->and($phase->trialPeriodDays())->toBeNull()
        ->and($phase->defaultTaxRates())->toBeNull();
});

test("converts to array with all fields", function (): void {
    $now = CarbonImmutable::now();
    $endDate = $now->addMonth();
    $trialEnd = $now->addDays(7);
    $items = collect([
        ["price" => "price_test123", "quantity" => 1],
    ]);
    $taxRates = collect(["txr_test123"]);

    $phase = StripeSubscriptionSchedulePhase::make(
        startDate: $now,
        endDate: $endDate,
        items: $items,
        iterations: 2,
        prorationBehavior: SubscriptionScheduleProrationBehavior::CreateProrations,
        trialPeriodDays: 7,
        trialEnd: $trialEnd,
        defaultPaymentMethod: "pm_test123",
        defaultTaxRates: $taxRates,
        collectionMethod: "charge_automatically",
        invoiceSettings: "settings",
        metadata: ["key" => "value"],
    );

    $array = $phase->toArray();

    expect($array)
        ->toHaveKey("start_date", $now->timestamp)
        ->toHaveKey("end_date", $endDate->timestamp)
        ->toHaveKey("items", $items->toArray())
        ->toHaveKey("iterations", 2)
        ->toHaveKey("proration_behavior", "create_prorations")
        ->toHaveKey("trial_period_days", 7)
        ->toHaveKey("trial_end", $trialEnd->timestamp)
        ->toHaveKey("default_payment_method", "pm_test123")
        ->toHaveKey("default_tax_rates", $taxRates->toArray())
        ->toHaveKey("collection_method", "charge_automatically")
        ->toHaveKey("invoice_settings", "settings")
        ->toHaveKey("metadata", ["key" => "value"]);
});

test("filters null values in toArray", function (): void {
    $phase = StripeSubscriptionSchedulePhase::make(
        startDate: CarbonImmutable::now(),
        endDate: null,
        iterations: null,
    );

    $array = $phase->toArray();

    expect($array)
        ->toHaveKey("start_date")
        ->not()->toHaveKey("end_date")
        ->not()->toHaveKey("iterations");
});

test("formats timestamps correctly in toArray", function (): void {
    $startDate = CarbonImmutable::createFromTimestamp(1640995200);
    $endDate = CarbonImmutable::createFromTimestamp(1643673600);

    $phase = StripeSubscriptionSchedulePhase::make(
        startDate: $startDate,
        endDate: $endDate,
    );

    $array = $phase->toArray();

    expect($array["start_date"])->toBe(1640995200)
        ->and($array["end_date"])->toBe(1643673600);
});