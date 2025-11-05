<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleEndBehavior;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleProrationBehavior;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleStatus;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripePhaseItem;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedule;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedulePhase;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;
use Illuminate\Support\Collection;
use Stripe\Util\Util;

test("can create StripeSubscriptionSchedule using make method", function (): void {
    $now = CarbonImmutable::now();
    $endDate = $now->addMonth();
    $phases = collect([
        StripeSubscriptionSchedulePhase::make()
            ->withStartDate($now)
            ->withEndDate($endDate),
    ]);

    $schedule = StripeSubscriptionSchedule::make()
        ->withId("sub_sched_test123")
        ->withCustomer("cus_test123")
        ->withEndBehavior(SubscriptionScheduleEndBehavior::Release)
        ->withStatus(SubscriptionScheduleStatus::Active)
        ->withPhases($phases)
        ->withMetadata(["key" => "value"]);

    expect($schedule->id())->toBe("sub_sched_test123")
        ->and($schedule->customer())->toBe("cus_test123")
        ->and($schedule->endBehavior())->toBe(SubscriptionScheduleEndBehavior::Release)
        ->and($schedule->status())->toBe(SubscriptionScheduleStatus::Active)
        ->and($schedule->phases())->toBe($phases)
        ->and($schedule->metadata())->toBe(["key" => "value"]);
});

test("can create StripeSubscriptionSchedule with nullable parameters", function (): void {
    $schedule = StripeSubscriptionSchedule::make();

    expect($schedule->id())->toBeNull()
        ->and($schedule->customer())->toBeNull()
        ->and($schedule->endBehavior())->toBeNull()
        ->and($schedule->status())->toBeNull()
        ->and($schedule->created())->toBeNull()
        ->and($schedule->phases())->toBeNull()
        ->and($schedule->metadata())->toBeNull();
});

test("can convert from Stripe object with all fields", function (): void {
    $stripeObject = Util::convertToStripeObject(StripeFixtures::subscriptionSchedule([
        "id" => "sub_sched_test123",
        "customer" => "cus_test123",
        "status" => "active",
        "end_behavior" => "release",
        "created" => 1640995200, // 2022-01-01 00:00:00 UTC
        "canceled_at" => 1641081600, // 2022-01-02 00:00:00 UTC
        "metadata" => ["key" => "value"],
    ]), []);

    $schedule = StripeSubscriptionSchedule::fromStripeObject($stripeObject);

    expect($schedule->id())->toBe("sub_sched_test123")
        ->and($schedule->customer())->toBe("cus_test123")
        ->and($schedule->status())->toBe(SubscriptionScheduleStatus::Active)
        ->and($schedule->endBehavior())->toBe(SubscriptionScheduleEndBehavior::Release)
        ->and($schedule->created())->toBeInstanceOf(CarbonImmutable::class)
        ->and($schedule->created()->timestamp)->toBe(1640995200)
        ->and($schedule->canceledAt())->toBeInstanceOf(CarbonImmutable::class)
        ->and($schedule->canceledAt()->timestamp)->toBe(1641081600)
        ->and($schedule->metadata())->toBe(["key" => "value"]);
});

test("handles nested customer and subscription objects in fromStripeObject", function (): void {
    $stripeObject = Util::convertToStripeObject(StripeFixtures::subscriptionSchedule([
        "customer" => Util::convertToStripeObject(["id" => "cus_from_object"], []),
        "subscription" => Util::convertToStripeObject(["id" => "sub_from_object"], []),
        "released_subscription" => "sub_released_from_object",
    ]), []);

    $schedule = StripeSubscriptionSchedule::fromStripeObject($stripeObject);

    expect($schedule->customer())->toBe("cus_from_object")
        ->and($schedule->subscription())->toBe("sub_from_object")
        ->and($schedule->releasedSubscription())->toBe("sub_released_from_object");
});

test("converts phases collection from Stripe object", function (): void {
    $stripeObject = Util::convertToStripeObject(StripeFixtures::subscriptionSchedule([
        "phases" => [
            [
                "start_date" => 1640995200,
                "end_date" => 1643673600,
                "items" => [
                    "data" => [
                        [
                            "price" => "price_test123",
                            "quantity" => 2,
                            "metadata" => ["phase" => "1"],
                        ],
                    ],
                ],
                "proration_behavior" => "none",
            ],
        ],
    ]), []);

    $schedule = StripeSubscriptionSchedule::fromStripeObject($stripeObject);

    expect($schedule->phases())->toHaveCount(1)
        ->and($schedule->phases()->first())->toBeInstanceOf(StripeSubscriptionSchedulePhase::class)
        ->and($schedule->phases()->first()->startDate())->toBeInstanceOf(CarbonImmutable::class)
        ->and($schedule->phases()->first()->endDate())->toBeInstanceOf(CarbonImmutable::class)
        ->and($schedule->phases()->first()->prorationBehavior())->toBe(SubscriptionScheduleProrationBehavior::None)
        ->and($schedule->phases()->first()->items())->toHaveCount(1)
        ->and($schedule->phases()->first()->items()->first()["price"])->toBe("price_test123")
        ->and($schedule->phases()->first()->items()->first()["quantity"])->toBe(2);
});

test("converts to array with all fields", function (): void {
    $now = CarbonImmutable::now();
    $endDate = $now->addMonth();
    $phases = collect([
        StripeSubscriptionSchedulePhase::make(
            startDate: $now,
            endDate: $endDate,
            prorationBehavior: SubscriptionScheduleProrationBehavior::CreateProrations,
        ),
    ]);

    $schedule = StripeSubscriptionSchedule::make(
        id: "sub_sched_test123",
        customer: "cus_test123",
        endBehavior: SubscriptionScheduleEndBehavior::Cancel,
        status: SubscriptionScheduleStatus::Active,
        created: $now,
        phases: $phases,
        metadata: ["key" => "value"],
    );

    $array = $schedule->toArray();

    expect($array)
        ->toHaveKey("id", "sub_sched_test123")
        ->toHaveKey("customer", "cus_test123")
        ->toHaveKey("end_behavior", "cancel")
        ->toHaveKey("status", "active")
        ->toHaveKey("created", $now->timestamp)
        ->toHaveKey("phases")
        ->toHaveKey("metadata", ["key" => "value"]);

    expect($array["phases"])->toHaveCount(1)
        ->and($array["phases"][0])->toBeArray()
        ->and($array["phases"][0]["proration_behavior"])->toBe("create_prorations");
});

test("filters null values in toArray", function (): void {
    $schedule = StripeSubscriptionSchedule::make(
        id: "sub_sched_test123",
        customer: null,
        endBehavior: null,
    );

    $array = $schedule->toArray();

    expect($array)
        ->toHaveKey("id")
        ->not()->toHaveKey("customer")
        ->not()->toHaveKey("end_behavior");
});

test("formats timestamps correctly in toArray", function (): void {
    $now = CarbonImmutable::createFromTimestamp(1640995200);
    $schedule = StripeSubscriptionSchedule::make(
        created: $now,
        canceledAt: $now->addDay(),
    );

    $array = $schedule->toArray();

    expect($array["created"])->toBe(1640995200)
        ->and($array["canceled_at"])->toBe(1641081600);
});

describe("create", function (): void {
    test("creates schedule from subscription using fromSubscription", function (): void {
        $fake = Stripe::fake([
            StripeMethod::SubscriptionSchedulesCreate->value => StripeFixtures::subscriptionSchedule([
                "id" => "sub_sched_new",
                "subscription" => "sub_123",
                "customer" => "cus_123",
            ]),
        ]);

        $subscription = StripeSubscription::make()
            ->withId("sub_123")
            ->withCustomer("cus_123");

        $newSchedule = $subscription->schedule();

        expect($newSchedule)->toBeNull();

        $createdSchedule = StripeSubscriptionSchedule::make()
            ->setParentSubscription($subscription)
            ->create();

        expect($createdSchedule)
            ->toBeInstanceOf(StripeSubscriptionSchedule::class)
            ->and($createdSchedule->id())->toBe("sub_sched_new")
            ->and($createdSchedule->subscription())->toBe("sub_123")
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::SubscriptionSchedulesCreate);

        $actualParams = $fake->getCall(StripeMethod::SubscriptionSchedulesCreate->value);
        expect($actualParams)->toHaveKey("from_subscription", "sub_123");
    });

    test("throws exception when parent subscription has no ID", function (): void {
        $subscription = StripeSubscription::make()->withCustomer("cus_123");

        $schedule = StripeSubscriptionSchedule::make()->setParentSubscription($subscription);

        expect(fn() => $schedule->create())->toThrow(InvalidArgumentException::class);
    });
});

describe("addPhase", function (): void {
    test("adds phase to empty schedule", function (): void {
        $schedule = StripeSubscriptionSchedule::make()
            ->withPhases(collect([]));

        $phaseItem = StripePhaseItem::make()
            ->withPrice("price_123")
            ->withQuantity(1);

        $result = $schedule->addPhase($phaseItem);

        expect($result)->toBe($schedule)
            ->and($schedule->phases())->toHaveCount(1)
            ->and($schedule->phases()->first())->toBeInstanceOf(StripeSubscriptionSchedulePhase::class)
            ->and($schedule->phases()->first()->items())->toHaveCount(1);
    });

    test("adds phase to existing phases", function (): void {
        $existingPhase = StripeSubscriptionSchedulePhase::make()
            ->withItems(collect([
                StripePhaseItem::make()
                    ->withPrice("price_existing")
                    ->withQuantity(1),
            ]));

        $schedule = StripeSubscriptionSchedule::make()
            ->withPhases(collect([$existingPhase]));

        $newPhaseItem = StripePhaseItem::make()
            ->withPrice("price_new")
            ->withQuantity(2);

        $schedule->addPhase($newPhaseItem);

        expect($schedule->phases())->toHaveCount(2)
            ->and($schedule->phases()->last()->items()->first()->price())->toBe("price_new")
            ->and($schedule->phases()->last()->items()->first()->quantity())->toBe(2);
    });

    test("initializes phases collection if null", function (): void {
        $schedule = StripeSubscriptionSchedule::make();

        expect($schedule->phases())->toBeNull();

        $phaseItem = StripePhaseItem::make()
            ->withPrice("price_123")
            ->withQuantity(1);

        $schedule->addPhase($phaseItem);

        expect($schedule->phases())->toBeInstanceOf(Collection::class)
            ->and($schedule->phases())->toHaveCount(1);
    });
});

describe("save", function (): void {
    test("creates new schedule when ID is null", function (): void {
        Stripe::fake([
            StripeMethod::SubscriptionSchedulesCreate->value => StripeFixtures::subscriptionSchedule([
                "id" => "sub_sched_new",
                "customer" => "cus_123",
                "subscription" => "sub_123",
            ]),
        ]);

        $schedule = StripeSubscriptionSchedule::make()
            ->withCustomer("cus_123")
            ->withSubscription("sub_123");

        $result = $schedule->save();

        expect($result)->toBeInstanceOf(StripeSubscriptionSchedule::class)
            ->and($result->id())->toBe("sub_sched_new")
            ->and($result)->not()->toBe($schedule);
    });

    test("updates existing schedule when ID is present", function (): void {
        Stripe::fake([
            StripeMethod::SubscriptionSchedulesUpdate->value => StripeFixtures::subscriptionSchedule([
                "id" => "sub_sched_123",
                "end_behavior" => "cancel",
            ]),
        ]);

        $schedule = StripeSubscriptionSchedule::make()
            ->withId("sub_sched_123")
            ->withEndBehavior(SubscriptionScheduleEndBehavior::Cancel);

        $result = $schedule->save();

        expect($result)->toBeInstanceOf(StripeSubscriptionSchedule::class)
            ->and($result->id())->toBe("sub_sched_123")
            ->and($result->endBehavior())->toBe(SubscriptionScheduleEndBehavior::Cancel)
            ->and($result)->not()->toBe($schedule);
    });

    test("preserves parent subscription reference", function (): void {
        Stripe::fake([
            StripeMethod::SubscriptionSchedulesCreate->value => StripeFixtures::subscriptionSchedule([
                "id" => "sub_sched_new",
                "customer" => "cus_123",
            ]),
        ]);

        $subscription = StripeSubscription::make()
            ->withId("sub_123")
            ->withCustomer("cus_123");

        $schedule = StripeSubscriptionSchedule::make()
            ->setParentSubscription($subscription)
            ->withCustomer("cus_123");

        $result = $schedule->save();

        expect($result)->toBeInstanceOf(StripeSubscriptionSchedule::class);
    });
});