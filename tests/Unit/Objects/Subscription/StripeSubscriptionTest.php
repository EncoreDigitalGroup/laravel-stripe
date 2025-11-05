<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Enums\CollectionMethod;
use EncoreDigitalGroup\Stripe\Enums\ProrationBehavior;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionStatus;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedule;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeBillingCycleAnchorConfig;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscriptionItem;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;
use Illuminate\Support\Collection;
use Stripe\Util\Util;

test("can create StripeSubscription using make method", function (): void {
    $items = collect([
        StripeSubscriptionItem::make()
            ->withPrice("price_123")
            ->withQuantity(1),
    ]);

    $subscription = StripeSubscription::make()
        ->withCustomer("cus_123")
        ->withStatus(SubscriptionStatus::Active)
        ->withItems($items);

    expect($subscription)
        ->toBeInstanceOf(StripeSubscription::class)
        ->and($subscription->customer())->toBe("cus_123")
        ->and($subscription->status())->toBe(SubscriptionStatus::Active)
        ->and($subscription->items())->toBeInstanceOf(Collection::class);
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
        ->and($subscription->id())->toBe("sub_123")
        ->and($subscription->customer())->toBe("cus_123")
        ->and($subscription->status())->toBe(SubscriptionStatus::Active)
        ->and($subscription->currentPeriodStart())->toBeInstanceOf(CarbonImmutable::class)
        ->and($subscription->currentPeriodStart()->timestamp)->toBe(1234567890)
        ->and($subscription->currentPeriodEnd())->toBeInstanceOf(CarbonImmutable::class)
        ->and($subscription->currentPeriodEnd()->timestamp)->toBe(1237159890)
        ->and($subscription->items())->toBeInstanceOf(Collection::class)
        ->and($subscription->items())->toHaveCount(1)
        ->and($subscription->defaultPaymentMethod())->toBe("pm_123")
        ->and($subscription->collectionMethod())->toBe(CollectionMethod::ChargeAutomatically)
        ->and($subscription->cancelAtPeriodEnd())->toBeFalse()
        ->and($subscription->description())->toBe("Test Subscription");
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

    expect($subscription->customer())->toBe("cus_123");
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

    expect($subscription->defaultPaymentMethod())->toBe("pm_123");
});

test("toArray converts enums to values", function (): void {
    $subscription = StripeSubscription::make()
        ->withCustomer("cus_123")
        ->withStatus(SubscriptionStatus::Active)
        ->withCollectionMethod(CollectionMethod::ChargeAutomatically);

    $array = $subscription->toArray();

    expect($array)->toBeArray()
        ->and($array["status"])->toBe("active")
        ->and($array["collection_method"])->toBe("charge_automatically");
});

test("toArray filters null values", function (): void {
    $subscription = StripeSubscription::make()
        ->withCustomer("cus_123");

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

    expect($subscription->items())->toBeInstanceOf(Collection::class)
        ->and($subscription->items())->toHaveCount(2)
        ->and($subscription->items()->get(0))->toBeInstanceOf(StripeSubscriptionItem::class)
        ->and($subscription->items()->get(0)->price())->toBe("price_1")
        ->and($subscription->items()->get(0)->quantity())->toBe(2)
        ->and($subscription->items()->get(1)->price())->toBe("price_2");
});

test("fromStripeObject handles item current period dates", function (): void {
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
                    "quantity" => 1,
                    "current_period_start" => 1704110400,
                    "current_period_end" => 1706788800,
                    "metadata" => [],
                ],
            ],
        ],
        "metadata" => [],
    ], []);

    $subscription = StripeSubscription::fromStripeObject($stripeObject);
    $item = $subscription->items()->get(0);

    expect($item->currentPeriodStart())->toBeInstanceOf(CarbonImmutable::class)
        ->and($item->currentPeriodStart()->timestamp)->toBe(1704110400)
        ->and($item->currentPeriodEnd())->toBeInstanceOf(CarbonImmutable::class)
        ->and($item->currentPeriodEnd()->timestamp)->toBe(1706788800);
});

test("fromStripeObject handles items without current period dates", function (): void {
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
                    "quantity" => 1,
                    "metadata" => [],
                ],
            ],
        ],
        "metadata" => [],
    ], []);

    $subscription = StripeSubscription::fromStripeObject($stripeObject);
    $item = $subscription->items()->get(0);

    expect($item->currentPeriodStart())->toBeNull()
        ->and($item->currentPeriodEnd())->toBeNull();
});

test("StripeSubscriptionItem toArray includes current period dates as timestamps", function (): void {
    $start = CarbonImmutable::create(2025, 1, 1, 12);
    $end = CarbonImmutable::create(2025, 2, 1, 12);

    $item = StripeSubscriptionItem::make()
        ->withPrice("price_123")
        ->withQuantity(2)
        ->withCurrentPeriodStart($start)
        ->withCurrentPeriodEnd($end);

    $array = $item->toArray();

    expect($array)->toBeArray()
        ->and($array["current_period_start"])->toBe($start->getTimestamp())
        ->and($array["current_period_end"])->toBe($end->getTimestamp());
});

test("StripeSubscriptionItem toArray excludes null current period dates", function (): void {
    $item = StripeSubscriptionItem::make()
        ->withPrice("price_123")
        ->withQuantity(1);

    $array = $item->toArray();

    expect($array)->not->toHaveKey("current_period_start")
        ->and($array)->not->toHaveKey("current_period_end");
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

    expect($subscription->billingCycleAnchorConfig())
        ->toBeInstanceOf(StripeBillingCycleAnchorConfig::class)
        ->and($subscription->billingCycleAnchorConfig()->dayOfMonth())->toBe(15)
        ->and($subscription->billingCycleAnchorConfig()->month())->toBe(6)
        ->and($subscription->billingCycleAnchorConfig()->hour())->toBe(14)
        ->and($subscription->billingCycleAnchorConfig()->minute())->toBe(30)
        ->and($subscription->billingCycleAnchorConfig()->second())->toBe(0);
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

    expect($subscription->prorationBehavior())->toBe(ProrationBehavior::None);
});

test("toArray includes billing_cycle_anchor_config", function (): void {
    $config = StripeBillingCycleAnchorConfig::make()
        ->withDayOfMonth(1)
        ->withHour(0)
        ->withMinute(0)
        ->withSecond(0);

    $subscription = StripeSubscription::make()
        ->withCustomer("cus_123")
        ->withBillingCycleAnchorConfig($config);

    $array = $subscription->toArray();

    expect($array)->toHaveKey("billing_cycle_anchor_config")
        ->and($array["billing_cycle_anchor_config"])->toBeArray()
        ->and($array["billing_cycle_anchor_config"]["day_of_month"])->toBe(1)
        ->and($array["billing_cycle_anchor_config"]["hour"])->toBe(0);
});

test("toArray includes proration_behavior", function (): void {
    $subscription = StripeSubscription::make()
        ->withCustomer("cus_123")
        ->withProrationBehavior(ProrationBehavior::None);

    $array = $subscription->toArray();

    expect($array)->toHaveKey("proration_behavior")
        ->and($array["proration_behavior"])->toBe("none");
});

test("issueFirstInvoiceOn creates billing cycle anchor config", function (): void {
    $subscription = StripeSubscription::make()->withCustomer("cus_123");

    $date = CarbonImmutable::create(2025, 6, 15, 14, 30, 0);
    $subscription->issueFirstInvoiceOn($date);

    expect($subscription->billingCycleAnchorConfig())
        ->toBeInstanceOf(StripeBillingCycleAnchorConfig::class)
        ->and($subscription->billingCycleAnchorConfig()->dayOfMonth())->toBe(15)
        ->and($subscription->billingCycleAnchorConfig()->month())->toBe(6)
        ->and($subscription->billingCycleAnchorConfig()->hour())->toBe(14)
        ->and($subscription->billingCycleAnchorConfig()->minute())->toBe(30)
        ->and($subscription->billingCycleAnchorConfig()->second())->toBe(0);
});

test("issueFirstInvoiceOn returns self for chaining", function (): void {
    $subscription = StripeSubscription::make()->withCustomer("cus_123");

    $date = CarbonImmutable::create(2025, 6, 15);
    $result = $subscription->issueFirstInvoiceOn($date);

    expect($result)->toBe($subscription);
});

describe("schedule", function (): void {
    test("returns null when subscription has no schedule", function (): void {
        $subscription = StripeSubscription::make()
            ->withId("sub_123")
            ->withCustomer("cus_123");

        $schedule = $subscription->schedule();

        expect($schedule)->toBeNull();
    });

    test("retrieves schedule when subscription has schedule ID", function (): void {
        Stripe::fake([
            StripeMethod::SubscriptionSchedulesRetrieve->value => StripeFixtures::subscriptionSchedule([
                "id" => "sub_sched_123",
                "subscription" => "sub_123",
            ]),
        ]);

        $subscription = StripeSubscription::fromStripeObject(
            Util::convertToStripeObject(StripeFixtures::subscription([
                "id" => "sub_123",
                "customer" => "cus_123",
                "schedule" => "sub_sched_123",
            ]), [])
        );

        $schedule = $subscription->schedule();

        expect($schedule)->toBeInstanceOf(StripeSubscriptionSchedule::class)
            ->and($schedule->id())->toBe("sub_sched_123");
    });

    test("caches schedule instance on subsequent calls", function (): void {
        Stripe::fake([
            StripeMethod::SubscriptionSchedulesRetrieve->value => StripeFixtures::subscriptionSchedule([
                "id" => "sub_sched_123",
                "subscription" => "sub_123",
            ]),
        ]);

        $subscription = StripeSubscription::fromStripeObject(
            Util::convertToStripeObject(StripeFixtures::subscription([
                "id" => "sub_123",
                "customer" => "cus_123",
                "schedule" => "sub_sched_123",
            ]), [])
        );

        $schedule1 = $subscription->schedule();
        $schedule2 = $subscription->schedule();

        expect($schedule1)->toBe($schedule2);
    });
});
