<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */
use EncoreDigitalGroup\Stripe\Enums\BillingScheme;
use EncoreDigitalGroup\Stripe\Enums\PriceType;
use EncoreDigitalGroup\Stripe\Enums\RecurringInterval;
use EncoreDigitalGroup\Stripe\Enums\RecurringUsageType;
use EncoreDigitalGroup\Stripe\Enums\TaxBehavior;
use EncoreDigitalGroup\Stripe\Enums\TiersMode;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeCustomUnitAmount;
use EncoreDigitalGroup\Stripe\Objects\Product\StripePrice;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeRecurring;
use Illuminate\Support\Collection;
use Stripe\Util\Util;

test("can create StripePrice using make method", function (): void {
    $price = StripePrice::make(
        product: "prod_123",
        unitAmount: 2000,
        currency: "usd",
        active: true
    );

    expect($price)
        ->toBeInstanceOf(StripePrice::class)
        ->and($price->product())->toBe("prod_123")
        ->and($price->unitAmount())->toBe(2000)
        ->and($price->currency())->toBe("usd")
        ->and($price->active())->toBeTrue();
});

test("can create StripePrice from Stripe object with recurring", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "price_123",
        "object" => "price",
        "product" => "prod_123",
        "active" => true,
        "currency" => "usd",
        "unit_amount" => 2000,
        "type" => "recurring",
        "billing_scheme" => "per_unit",
        "recurring" => [
            "interval" => "month",
            "interval_count" => 1,
            "trial_period_days" => 14,
            "usage_type" => "licensed",
        ],
        "nickname" => "Monthly Plan",
        "metadata" => ["key" => "value"],
        "lookup_key" => "monthly_plan",
        "tax_behavior" => "exclusive",
        "created" => 1234567890,
    ], []);

    $price = StripePrice::fromStripeObject($stripeObject);

    expect($price)
        ->toBeInstanceOf(StripePrice::class)
        ->and($price->id())->toBe("price_123")
        ->and($price->product())->toBe("prod_123")
        ->and($price->type())->toBe(PriceType::Recurring)
        ->and($price->billingScheme())->toBe(BillingScheme::PerUnit)
        ->and($price->recurring())->toBeInstanceOf(StripeRecurring::class)
        ->and($price->recurring()->interval())->toBe(RecurringInterval::Month)
        ->and($price->recurring()->intervalCount())->toBe(1)
        ->and($price->recurring()->trialPeriodDays())->toBe(14)
        ->and($price->recurring()->usageType())->toBe(RecurringUsageType::Licensed)
        ->and($price->taxBehavior())->toBe(TaxBehavior::Exclusive);
});

test("fromStripeObject handles nested product object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "price_123",
        "object" => "price",
        "product" => [
            "id" => "prod_123",
            "object" => "product",
        ],
        "currency" => "usd",
        "metadata" => [],
    ], []);

    $price = StripePrice::fromStripeObject($stripeObject);

    expect($price->product())->toBe("prod_123");
});

test("toArray converts recurring enums to values", function (): void {
    $price = StripePrice::make(
        product: "prod_123",
        unitAmount: 2000,
        currency: "usd",
        type: PriceType::Recurring,
        billingScheme: BillingScheme::PerUnit,
        recurring: StripeRecurring::make(
            interval: RecurringInterval::Month,
            intervalCount: 1
        )
    );

    $array = $price->toArray();

    expect($array)->toBeArray()
        ->and($array["type"])->toBe("recurring")
        ->and($array["billing_scheme"])->toBe("per_unit")
        ->and($array["recurring"]["interval"])->toBe("month");
});

test("toArray filters null values", function (): void {
    $price = StripePrice::make(
        product: "prod_123",
        unitAmount: 2000,
        currency: "usd"
    );

    $array = $price->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("product")
        ->and($array)->toHaveKey("unit_amount")
        ->and($array)->toHaveKey("currency")
        ->and($array)->not->toHaveKey("id")
        ->and($array)->not->toHaveKey("nickname");
});

test("fromStripeObject handles tiers", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "price_123",
        "object" => "price",
        "product" => "prod_123",
        "currency" => "usd",
        "billing_scheme" => "tiered",
        "tiers_mode" => "graduated",
        "tiers" => [
            [
                "up_to" => 10,
                "unit_amount" => 1000,
                "flat_amount" => 0,
            ],
            [
                "up_to" => null,
                "unit_amount" => 800,
                "flat_amount" => 0,
            ],
        ],
        "metadata" => [],
    ], []);

    $price = StripePrice::fromStripeObject($stripeObject);

    expect($price->tiers())->toBeInstanceOf(Collection::class)
        ->and($price->tiers())->toHaveCount(2)
        ->and($price->tiersMode())->toBe(TiersMode::Graduated);
});

test("fromStripeObject handles custom unit amount", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "price_123",
        "object" => "price",
        "product" => "prod_123",
        "currency" => "usd",
        "custom_unit_amount" => [
            "minimum" => 500,
            "maximum" => 5000,
            "preset" => 1000,
        ],
        "metadata" => [],
    ], []);

    $price = StripePrice::fromStripeObject($stripeObject);

    expect($price->customUnitAmount())->toBeInstanceOf(StripeCustomUnitAmount::class)
        ->and($price->customUnitAmount()->minimum())->toBe(500)
        ->and($price->customUnitAmount()->maximum())->toBe(5000)
        ->and($price->customUnitAmount()->preset())->toBe(1000);
});
