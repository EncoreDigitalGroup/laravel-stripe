<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Enums\SubscriptionStatus;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\SubscriptionBuilder;

describe("SubscriptionBuilder", function (): void {
    test("can build a basic subscription", function (): void {
        $builder = new SubscriptionBuilder();
        $subscription = $builder->build(
            customer: "cus_123",
            items: [["price" => "price_123"]]
        );

        expect($subscription)
            ->toBeInstanceOf(StripeSubscription::class)
            ->and($subscription->customer)->toBe("cus_123")
            ->and($subscription->items)->toBe([["price" => "price_123"]]);
    });

    test("can build subscription with all parameters", function (): void {
        $builder = new SubscriptionBuilder();
        $currentPeriodStart = CarbonImmutable::createFromTimestamp(1640995200);
        $currentPeriodEnd = CarbonImmutable::createFromTimestamp(1643673600);
        $cancelAt = CarbonImmutable::createFromTimestamp(1650000000);
        $subscription = $builder->build(
            id: "sub_123",
            customer: "cus_456",
            status: SubscriptionStatus::Active,
            currentPeriodStart: $currentPeriodStart,
            currentPeriodEnd: $currentPeriodEnd,
            items: [
                ["price" => "price_123", "quantity" => 2],
                ["price" => "price_456", "quantity" => 1]
            ],
            metadata: ["plan" => "premium"],
            cancelAt: $cancelAt,
            canceledAt: null,
            cancelAtPeriodEnd: false
        );

        expect($subscription)
            ->toBeInstanceOf(StripeSubscription::class)
            ->and($subscription->id)->toBe("sub_123")
            ->and($subscription->customer)->toBe("cus_456")
            ->and($subscription->status)->toBe(SubscriptionStatus::Active)
            ->and($subscription->currentPeriodStart)->toBe($currentPeriodStart)
            ->and($subscription->currentPeriodEnd)->toBe($currentPeriodEnd)
            ->and($subscription->items)->toBe([
                ["price" => "price_123", "quantity" => 2],
                ["price" => "price_456", "quantity" => 1]
            ])
            ->and($subscription->metadata)->toBe(["plan" => "premium"])
            ->and($subscription->cancelAt)->toBe($cancelAt)
            ->and($subscription->canceledAt)->toBeNull()
            ->and($subscription->cancelAtPeriodEnd)->toBeFalse();
    });

    test("can build subscription with minimal parameters", function (): void {
        $builder = new SubscriptionBuilder();
        $subscription = $builder->build();

        expect($subscription)
            ->toBeInstanceOf(StripeSubscription::class)
            ->and($subscription->id)->toBeNull()
            ->and($subscription->customer)->toBeNull()
            ->and($subscription->items)->toBeNull();
    });
});