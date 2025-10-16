<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Enums\ProrationBehavior;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeBillingCycleAnchorConfig;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Stripe\Services\StripeSubscriptionService;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

test("can create a subscription", function (): void {
    $fake = Stripe::fake([
        StripeMethod::SubscriptionsCreate->value => StripeFixtures::subscription([
            "id" => "sub_test123",
            "customer" => "cus_test",
            "status" => "active",
        ]),
    ]);

    $subscription = StripeSubscription::make(
        customer: "cus_test",
        items: [
            ["price" => "price_test", "quantity" => 1],
        ]
    );

    $service = StripeSubscriptionService::make();
    $result = $service->create($subscription);

    expect($result)
        ->toBeInstanceOf(StripeSubscription::class)
        ->and($result->id)->toBe("sub_test123")
        ->and($result->customer)->toBe("cus_test")
        ->and($fake)->toHaveCalledStripeMethod(StripeMethod::SubscriptionsCreate);
});

test("can retrieve a subscription", function (): void {
    $fake = Stripe::fake([
        "subscriptions.retrieve" => StripeFixtures::subscription([
            "id" => "sub_existing",
            "customer" => "cus_test",
        ]),
    ]);

    $service = StripeSubscriptionService::make();
    $subscription = $service->get("sub_existing");

    expect($subscription)
        ->toBeInstanceOf(StripeSubscription::class)
        ->and($subscription->id)->toBe("sub_existing")
        ->and($subscription->customer)->toBe("cus_test")
        ->and($fake)->toHaveCalledStripeMethod("subscriptions.retrieve");
});

test("can update a subscription", function (): void {
    $fake = Stripe::fake([
        "subscriptions.update" => StripeFixtures::subscription([
            "id" => "sub_123",
            "description" => "Updated Description",
        ]),
    ]);

    $subscription = StripeSubscription::make(
        description: "Updated Description"
    );

    $service = StripeSubscriptionService::make();
    $result = $service->update("sub_123", $subscription);

    expect($result)
        ->toBeInstanceOf(StripeSubscription::class)
        ->and($result->description)->toBe("Updated Description")
        ->and($fake)->toHaveCalledStripeMethod("subscriptions.update");
});

test("can cancel a subscription immediately", function (): void {
    $fake = Stripe::fake([
        "subscriptions.cancel" => StripeFixtures::subscription([
            "id" => "sub_123",
            "status" => "canceled",
            "canceled_at" => time(),
        ]),
    ]);

    $service = StripeSubscriptionService::make();
    $result = $service->cancelImmediately("sub_123");

    expect($result)
        ->toBeInstanceOf(StripeSubscription::class)
        ->and($result->id)->toBe("sub_123")
        ->and($fake)->toHaveCalledStripeMethod("subscriptions.cancel");
});

test("can cancel subscription at period end", function (): void {
    $fake = Stripe::fake([
        "subscriptions.update" => StripeFixtures::subscription([
            "id" => "sub_123",
            "cancel_at_period_end" => true,
        ]),
    ]);

    $service = StripeSubscriptionService::make();
    $result = $service->cancelAtPeriodEnd("sub_123");

    expect($result)
        ->toBeInstanceOf(StripeSubscription::class)
        ->and($result->cancelAtPeriodEnd)->toBeTrue()
        ->and($fake)->toHaveCalledStripeMethod("subscriptions.update", [
            "cancel_at_period_end" => true,
        ]);
});

test("can resume a canceled subscription", function (): void {
    $fake = Stripe::fake([
        "subscriptions.update" => StripeFixtures::subscription([
            "id" => "sub_123",
            "cancel_at_period_end" => false,
        ]),
    ]);

    $service = StripeSubscriptionService::make();
    $result = $service->resume("sub_123");

    expect($result)
        ->toBeInstanceOf(StripeSubscription::class)
        ->and($result->cancelAtPeriodEnd)->toBeFalse()
        ->and($fake)->toHaveCalledStripeMethod("subscriptions.update", [
            "cancel_at_period_end" => false,
        ]);
});

test("can list subscriptions", function (): void {
    $fake = Stripe::fake([
        "subscriptions.all" => StripeFixtures::subscriptionList([
            StripeFixtures::subscription(["id" => "sub_1", "customer" => "cus_1"]),
            StripeFixtures::subscription(["id" => "sub_2", "customer" => "cus_2"]),
            StripeFixtures::subscription(["id" => "sub_3", "customer" => "cus_3"]),
        ]),
    ]);

    $service = StripeSubscriptionService::make();
    $subscriptions = $service->list(["limit" => 10]);

    expect($subscriptions)
        ->toHaveCount(3)
        ->and($subscriptions->first())->toBeInstanceOf(StripeSubscription::class)
        ->and($subscriptions->first()->id)->toBe("sub_1")
        ->and($fake)->toHaveCalledStripeMethod("subscriptions.all");
});

test("can search subscriptions", function (): void {
    $fake = Stripe::fake([
        "subscriptions.search" => StripeFixtures::subscriptionList([
            StripeFixtures::subscription(["id" => "sub_1", "customer" => "cus_search"]),
        ]),
    ]);

    $service = StripeSubscriptionService::make();
    $subscriptions = $service->search('customer:"cus_search"');

    expect($subscriptions)
        ->toHaveCount(1)
        ->and($subscriptions->first())->toBeInstanceOf(StripeSubscription::class)
        ->and($subscriptions->first()->customer)->toBe("cus_search")
        ->and($fake)->toHaveCalledStripeMethod("subscriptions.search");
});

test("create removes id from payload", function (): void {
    $fake = Stripe::fake([
        "subscriptions.create" => StripeFixtures::subscription(["id" => "sub_new"]),
    ]);

    $subscription = StripeSubscription::make(
        id: "should_be_removed",
        customer: "cus_test",
        items: [["price" => "price_test"]]
    );

    $service = StripeSubscriptionService::make();
    $service->create($subscription);

    $params = $fake->getCall("subscriptions.create");

    expect($params)->not->toHaveKey("id")
        ->and($params)->toHaveKey("customer");
});

test("update removes id from payload", function (): void {
    $fake = Stripe::fake([
        "subscriptions.update" => StripeFixtures::subscription(["id" => "sub_123"]),
    ]);

    $subscription = StripeSubscription::make(
        id: "should_be_removed",
        description: "Updated"
    );

    $service = StripeSubscriptionService::make();
    $service->update("sub_123", $subscription);

    $params = $fake->getCall("subscriptions.update");

    expect($params)->not->toHaveKey("id")
        ->and($params)->toHaveKey("description");
});

test("create sends billing_cycle_anchor_config when set", function (): void {
    $fake = Stripe::fake([
        "subscriptions.create" => StripeFixtures::subscription([
            "id" => "sub_new",
            "billing_cycle_anchor_config" => [
                "day_of_month" => 15,
                "hour" => 0,
                "minute" => 0,
                "second" => 0,
            ],
        ]),
    ]);

    $config = StripeBillingCycleAnchorConfig::make(
        dayOfMonth: 15,
        hour: 0,
        minute: 0,
        second: 0
    );

    $subscription = StripeSubscription::make(
        customer: "cus_test",
        items: [["price" => "price_test"]],
        billingCycleAnchorConfig: $config
    );

    $service = StripeSubscriptionService::make();
    $service->create($subscription);

    $params = $fake->getCall("subscriptions.create");

    expect($params)->toHaveKey("billing_cycle_anchor_config")
        ->and($params["billing_cycle_anchor_config"])->toBeArray()
        ->and($params["billing_cycle_anchor_config"]["day_of_month"])->toBe(15)
        ->and($params["billing_cycle_anchor_config"]["hour"])->toBe(0);
});

test("create sends proration_behavior when set", function (): void {
    $fake = Stripe::fake([
        "subscriptions.create" => StripeFixtures::subscription([
            "id" => "sub_new",
            "proration_behavior" => "none",
        ]),
    ]);

    $subscription = StripeSubscription::make(
        customer: "cus_test",
        items: [["price" => "price_test"]],
        prorationBehavior: ProrationBehavior::None
    );

    $service = StripeSubscriptionService::make();
    $service->create($subscription);

    $params = $fake->getCall("subscriptions.create");

    expect($params)->toHaveKey("proration_behavior")
        ->and($params["proration_behavior"])->toBe("none");
});

test("issueFirstInvoiceOn works with service create", function (): void {
    $fake = Stripe::fake([
        "subscriptions.create" => StripeFixtures::subscription([
            "id" => "sub_new",
        ]),
    ]);

    $subscription = StripeSubscription::make(
        customer: "cus_test",
        items: [["price" => "price_test"]],
        trialEnd: CarbonImmutable::now()->addDays(14)
    );

    $subscription->issueFirstInvoiceOn(
        CarbonImmutable::create(2025, 6, 15, 0, 0, 0)
    );

    $service = StripeSubscriptionService::make();
    $service->create($subscription);

    $params = $fake->getCall("subscriptions.create");

    expect($params)->toHaveKey("billing_cycle_anchor_config")
        ->and($params["billing_cycle_anchor_config"]["day_of_month"])->toBe(15)
        ->and($params["billing_cycle_anchor_config"]["month"])->toBe(6);
});
