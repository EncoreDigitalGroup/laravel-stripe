<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleEndBehavior;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleStatus;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedule;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedulePhase;
use EncoreDigitalGroup\Stripe\Services\StripeSubscriptionScheduleService;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

describe("create", function (): void {
    test("creates subscription schedule successfully", function (): void {
        $fake = Stripe::fake([
            StripeMethod::SubscriptionSchedulesCreate->value => StripeFixtures::subscriptionSchedule([
                "id" => "sub_sched_test123",
                "customer" => "cus_test123",
                "status" => "not_started",
            ]),
        ]);

        $service = StripeSubscriptionScheduleService::make();
        $subscriptionSchedule = StripeSubscriptionSchedule::make()
            ->withCustomer("cus_test123")
            ->withEndBehavior(SubscriptionScheduleEndBehavior::Release)
            ->withPhases(collect([
                StripeSubscriptionSchedulePhase::make()
                    ->withStartDate(CarbonImmutable::now())
                    ->withEndDate(CarbonImmutable::now()->addMonth())
                    ->withItems(collect([
                        ["price" => "price_test123", "quantity" => 1],
                    ])),
            ]));

        $result = $service->create($subscriptionSchedule);

        expect($result)
            ->toBeInstanceOf(StripeSubscriptionSchedule::class)
            ->and($result->id())->toBe("sub_sched_test123")
            ->and($result->customer())->toBe("cus_test123")
            ->and($result->status())->toBe(SubscriptionScheduleStatus::NotStarted)
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::SubscriptionSchedulesCreate);
    });

    test("removes readonly fields from create request", function (): void {
        $fake = Stripe::fake([
            StripeMethod::SubscriptionSchedulesCreate->value => StripeFixtures::subscriptionSchedule(),
        ]);

        $service = StripeSubscriptionScheduleService::make();
        $subscriptionSchedule = StripeSubscriptionSchedule::make()
            ->withCustomer("cus_test123");

        $service->create($subscriptionSchedule);

        expect($fake)->toHaveCalledStripeMethod(StripeMethod::SubscriptionSchedulesCreate);

        $actualParams = $fake->getCall(StripeMethod::SubscriptionSchedulesCreate->value);
        expect($actualParams)
            ->not()->toHaveKey("id")
            ->not()->toHaveKey("created")
            ->not()->toHaveKey("status")
            ->toHaveKey("customer");
    });
});

describe("retrieve", function (): void {
    test("retrieves subscription schedule successfully", function (): void {
        $fake = Stripe::fake([
            StripeMethod::SubscriptionSchedulesRetrieve->value => StripeFixtures::subscriptionSchedule([
                "id" => "sub_sched_test123",
                "customer" => "cus_test123",
            ]),
        ]);

        $service = StripeSubscriptionScheduleService::make();
        $result = $service->get("sub_sched_test123");

        expect($result)
            ->toBeInstanceOf(StripeSubscriptionSchedule::class)
            ->and($result->id())->toBe("sub_sched_test123")
            ->and($result->customer())->toBe("cus_test123")
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::SubscriptionSchedulesRetrieve);
    });
});

describe("update", function (): void {
    test("updates subscription schedule successfully", function (): void {
        $fake = Stripe::fake([
            StripeMethod::SubscriptionSchedulesUpdate->value => StripeFixtures::subscriptionSchedule([
                "id" => "sub_sched_test123",
                "end_behavior" => "cancel",
            ]),
        ]);

        $service = StripeSubscriptionScheduleService::make();
        $subscriptionSchedule = StripeSubscriptionSchedule::make()
            ->withEndBehavior(SubscriptionScheduleEndBehavior::Cancel);

        $result = $service->update("sub_sched_test123", $subscriptionSchedule);

        expect($result)
            ->toBeInstanceOf(StripeSubscriptionSchedule::class)
            ->and($result->endBehavior())->toBe(SubscriptionScheduleEndBehavior::Cancel)
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::SubscriptionSchedulesUpdate);
    });

    test("removes readonly fields from update request", function (): void {
        $fake = Stripe::fake([
            StripeMethod::SubscriptionSchedulesUpdate->value => StripeFixtures::subscriptionSchedule(),
        ]);

        $service = StripeSubscriptionScheduleService::make();
        $subscriptionSchedule = StripeSubscriptionSchedule::make()
            ->withEndBehavior(SubscriptionScheduleEndBehavior::Cancel);

        $service->update("sub_sched_test123", $subscriptionSchedule);

        expect($fake)->toHaveCalledStripeMethod(StripeMethod::SubscriptionSchedulesUpdate);

        $actualParams = $fake->getCall(StripeMethod::SubscriptionSchedulesUpdate->value);
        expect($actualParams)
            ->not()->toHaveKey("id")
            ->not()->toHaveKey("created")
            ->not()->toHaveKey("status")
            ->not()->toHaveKey("customer")
            ->toHaveKey("end_behavior");
    });
});

describe("cancel", function (): void {
    test("cancels subscription schedule successfully", function (): void {
        $fake = Stripe::fake([
            StripeMethod::SubscriptionSchedulesCancel->value => StripeFixtures::subscriptionSchedule([
                "id" => "sub_sched_test123",
                "status" => "canceled",
                "canceled_at" => time(),
            ]),
        ]);

        $service = StripeSubscriptionScheduleService::make();
        $result = $service->cancel("sub_sched_test123");

        expect($result)
            ->toBeInstanceOf(StripeSubscriptionSchedule::class)
            ->and($result->id())->toBe("sub_sched_test123")
            ->and($result->status())->toBe(SubscriptionScheduleStatus::Canceled)
            ->and($result->canceledAt())->toBeInstanceOf(CarbonImmutable::class)
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::SubscriptionSchedulesCancel);
    });

    test("cancels with invoice now and prorate options", function (): void {
        $fake = Stripe::fake([
            StripeMethod::SubscriptionSchedulesCancel->value => StripeFixtures::subscriptionSchedule([
                "status" => "canceled",
            ]),
        ]);

        $service = StripeSubscriptionScheduleService::make();
        $service->cancel("sub_sched_test123", invoiceNow: true, prorate: false);

        expect($fake)->toHaveCalledStripeMethod(StripeMethod::SubscriptionSchedulesCancel);

        $actualParams = $fake->getCall(StripeMethod::SubscriptionSchedulesCancel->value);
        expect($actualParams)
            ->toHaveKey("invoice_now", true)
            ->toHaveKey("prorate", false);
    });
});

describe("release", function (): void {
    test("releases subscription schedule successfully", function (): void {
        $fake = Stripe::fake([
            StripeMethod::SubscriptionSchedulesRelease->value => StripeFixtures::subscriptionSchedule([
                "id" => "sub_sched_test123",
                "status" => "released",
                "released_at" => time(),
                "released_subscription" => "sub_test123",
            ]),
        ]);

        $service = StripeSubscriptionScheduleService::make();
        $result = $service->release("sub_sched_test123");

        expect($result)
            ->toBeInstanceOf(StripeSubscriptionSchedule::class)
            ->and($result->id())->toBe("sub_sched_test123")
            ->and($result->status())->toBe(SubscriptionScheduleStatus::Released)
            ->and($result->releasedAt())->toBeInstanceOf(CarbonImmutable::class)
            ->and($result->releasedSubscription())->toBe("sub_test123")
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::SubscriptionSchedulesRelease);
    });

    test("releases with preserve cancel date option", function (): void {
        $fake = Stripe::fake([
            StripeMethod::SubscriptionSchedulesRelease->value => StripeFixtures::subscriptionSchedule([
                "status" => "released",
            ]),
        ]);

        $service = StripeSubscriptionScheduleService::make();
        $service->release("sub_sched_test123", preserveCancelDate: true);

        expect($fake)->toHaveCalledStripeMethod(StripeMethod::SubscriptionSchedulesRelease);

        $actualParams = $fake->getCall(StripeMethod::SubscriptionSchedulesRelease->value);
        expect($actualParams)
            ->toHaveKey("preserve_cancel_date", true);
    });
});

describe("fromSubscription", function (): void {
    test("creates schedule from existing subscription", function (): void {
        $fake = Stripe::fake([
            StripeMethod::SubscriptionSchedulesCreate->value => StripeFixtures::subscriptionSchedule([
                "id" => "sub_sched_from_sub",
                "subscription" => "sub_123",
                "customer" => "cus_123",
            ]),
        ]);

        $service = StripeSubscriptionScheduleService::make();
        $result = $service->fromSubscription("sub_123");

        expect($result)
            ->toBeInstanceOf(StripeSubscriptionSchedule::class)
            ->and($result->id())->toBe("sub_sched_from_sub")
            ->and($result->subscription())->toBe("sub_123")
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::SubscriptionSchedulesCreate);

        $actualParams = $fake->getCall(StripeMethod::SubscriptionSchedulesCreate->value);
        expect($actualParams)->toHaveKey("from_subscription", "sub_123");
    });
});