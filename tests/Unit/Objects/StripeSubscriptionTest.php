<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Enums\CollectionMethod;
use EncoreDigitalGroup\Stripe\Enums\ProrationBehavior;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionStatus;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeBillingCycleAnchorConfig;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use Stripe\Util\Util;

test("can create StripeSubscription using make method", function (): void {
    $subscription = StripeSubscription::make(
        customer: "cus_123",
        status: SubscriptionStatus::Active,
        items: [
            ["price" => "price_123", "quantity" => 1],
        ]
    );

    expect($subscription)
        ->toBeInstanceOf(StripeSubscription::class)
        ->and($subscription->customer)->toBe("cus_123")
        ->and($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->items)->toBeArray();
});

test("can create StripeSubscription from Stripe object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "sub_123",
        "object" => "subscription",
        "customer" => "cus_123",
        "status" => "active",
        "current_period_start" => 1234567890,
        "current_period_end" => 1237159890,
        "cancel_at" => null,
        "canceled_at" => null,
        "trial_start" => null,
        "trial_end" => null,
        "items" => [
            "data" => [
                [
                    "id" => "si_123",
                    "price" => ["id" => "price_123"],
                    "quantity" => 1,
                    "metadata" => ["key" => "value"],
                ],
            ],
        ],
        "default_payment_method" => "pm_123",
        "metadata" => ["subscription_key" => "subscription_value"],
        "currency" => "usd",
        "collection_method" => "charge_automatically",
        "billing_cycle_anchor" => 1234567890,
        "cancel_at_period_end" => false,
        "days_until_due" => null,
        "description" => "Test Subscription",
    ], []);

    $subscription = StripeSubscription::fromStripeObject($stripeObject);

    expect($subscription)
        ->toBeInstanceOf(StripeSubscription::class)
        ->and($subscription->id)->toBe("sub_123")
        ->and($subscription->customer)->toBe("cus_123")
        ->and($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->currentPeriodStart)->toBe(1234567890)
        ->and($subscription->currentPeriodEnd)->toBe(1237159890)
        ->and($subscription->items)->toBeArray()
        ->and($subscription->items)->toHaveCount(1)
        ->and($subscription->defaultPaymentMethod)->toBe("pm_123")
        ->and($subscription->collectionMethod)->toBe(CollectionMethod::ChargeAutomatically)
        ->and($subscription->cancelAtPeriodEnd)->toBeFalse()
        ->and($subscription->description)->toBe("Test Subscription");
});

test("fromStripeObject handles nested customer object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "sub_123",
        "object" => "subscription",
        "customer" => [
            "id" => "cus_123",
            "object" => "customer",
        ],
        "status" => "active",
        "items" => ["data" => []],
        "metadata" => [],
    ], []);

    $subscription = StripeSubscription::fromStripeObject($stripeObject);

    expect($subscription->customer)->toBe("cus_123");
});

test("fromStripeObject handles nested payment method object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "sub_123",
        "object" => "subscription",
        "customer" => "cus_123",
        "status" => "active",
        "default_payment_method" => [
            "id" => "pm_123",
            "object" => "payment_method",
        ],
        "items" => ["data" => []],
        "metadata" => [],
    ], []);

    $subscription = StripeSubscription::fromStripeObject($stripeObject);

    expect($subscription->defaultPaymentMethod)->toBe("pm_123");
});

test("toArray converts enums to values", function (): void {
    $subscription = StripeSubscription::make(
        customer: "cus_123",
        status: SubscriptionStatus::Active,
        collectionMethod: CollectionMethod::ChargeAutomatically
    );

    $array = $subscription->toArray();

    expect($array)->toBeArray()
        ->and($array["status"])->toBe("active")
        ->and($array["collection_method"])->toBe("charge_automatically");
});

test("toArray filters null values", function (): void {
    $subscription = StripeSubscription::make(
        customer: "cus_123"
    );

    $array = $subscription->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("customer")
        ->and($array)->not->toHaveKey("id")
        ->and($array)->not->toHaveKey("status")
        ->and($array)->not->toHaveKey("description");
});

test("fromStripeObject handles items correctly", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "sub_123",
        "object" => "subscription",
        "customer" => "cus_123",
        "status" => "active",
        "items" => [
            "data" => [
                [
                    "id" => "si_1",
                    "price" => ["id" => "price_1"],
                    "quantity" => 2,
                    "metadata" => ["item_key" => "item_value"],
                ],
                [
                    "id" => "si_2",
                    "price" => ["id" => "price_2"],
                    "quantity" => 1,
                    "metadata" => [],
                ],
            ],
        ],
        "metadata" => [],
    ], []);

    $subscription = StripeSubscription::fromStripeObject($stripeObject);

    expect($subscription->items)->toBeArray()
        ->and($subscription->items)->toHaveCount(2)
        ->and($subscription->items[0]["price"])->toBe("price_1")
        ->and($subscription->items[0]["quantity"])->toBe(2)
        ->and($subscription->items[1]["price"])->toBe("price_2");
});

test("fromStripeObject handles billing_cycle_anchor_config", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "sub_123",
        "object" => "subscription",
        "customer" => "cus_123",
        "status" => "active",
        "billing_cycle_anchor_config" => [
            "day_of_month" => 15,
            "month" => 6,
            "hour" => 14,
            "minute" => 30,
            "second" => 0,
        ],
        "items" => ["data" => []],
        "metadata" => [],
    ], []);

    $subscription = StripeSubscription::fromStripeObject($stripeObject);

    expect($subscription->billingCycleAnchorConfig)
        ->toBeInstanceOf(StripeBillingCycleAnchorConfig::class)
        ->and($subscription->billingCycleAnchorConfig->dayOfMonth)->toBe(15)
        ->and($subscription->billingCycleAnchorConfig->month)->toBe(6)
        ->and($subscription->billingCycleAnchorConfig->hour)->toBe(14)
        ->and($subscription->billingCycleAnchorConfig->minute)->toBe(30)
        ->and($subscription->billingCycleAnchorConfig->second)->toBe(0);
});

test("fromStripeObject handles proration_behavior", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "sub_123",
        "object" => "subscription",
        "customer" => "cus_123",
        "status" => "active",
        "proration_behavior" => "none",
        "items" => ["data" => []],
        "metadata" => [],
    ], []);

    $subscription = StripeSubscription::fromStripeObject($stripeObject);

    expect($subscription->prorationBehavior)->toBe(ProrationBehavior::None);
});

test("toArray includes billing_cycle_anchor_config", function (): void {
    $config = StripeBillingCycleAnchorConfig::make(
        dayOfMonth: 1,
        hour: 0,
        minute: 0,
        second: 0
    );

    $subscription = StripeSubscription::make(
        customer: "cus_123",
        billingCycleAnchorConfig: $config
    );

    $array = $subscription->toArray();

    expect($array)->toHaveKey("billing_cycle_anchor_config")
        ->and($array["billing_cycle_anchor_config"])->toBeArray()
        ->and($array["billing_cycle_anchor_config"]["day_of_month"])->toBe(1)
        ->and($array["billing_cycle_anchor_config"]["hour"])->toBe(0);
});

test("toArray includes proration_behavior", function (): void {
    $subscription = StripeSubscription::make(
        customer: "cus_123",
        prorationBehavior: ProrationBehavior::None
    );

    $array = $subscription->toArray();

    expect($array)->toHaveKey("proration_behavior")
        ->and($array["proration_behavior"])->toBe("none");
});

test("issueFirstInvoiceOn creates billing cycle anchor config", function (): void {
    $subscription = StripeSubscription::make(customer: "cus_123");

    $date = CarbonImmutable::create(2025, 6, 15, 14, 30, 0);
    $subscription->issueFirstInvoiceOn($date);

    expect($subscription->billingCycleAnchorConfig)
        ->toBeInstanceOf(StripeBillingCycleAnchorConfig::class)
        ->and($subscription->billingCycleAnchorConfig->dayOfMonth)->toBe(15)
        ->and($subscription->billingCycleAnchorConfig->month)->toBe(6)
        ->and($subscription->billingCycleAnchorConfig->hour)->toBe(14)
        ->and($subscription->billingCycleAnchorConfig->minute)->toBe(30)
        ->and($subscription->billingCycleAnchorConfig->second)->toBe(0);
});

test("issueFirstInvoiceOn returns self for chaining", function (): void {
    $subscription = StripeSubscription::make(customer: "cus_123");

    $date = CarbonImmutable::create(2025, 6, 15);
    $result = $subscription->issueFirstInvoiceOn($date);

    expect($result)->toBe($subscription);
});
