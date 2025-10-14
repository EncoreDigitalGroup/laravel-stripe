<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Product\StripeProductTier;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\TierBuilder;

describe("TierBuilder", function (): void {
    test("can build a basic tier", function (): void {
        $builder = new TierBuilder();
        $tier = $builder->build(
            upTo: 1000,
            unitAmount: 100
        );

        expect($tier)
            ->toBeInstanceOf(StripeProductTier::class)
            ->and($tier->upTo)->toBe(1000)
            ->and($tier->unitAmount)->toBe(100);
    });

    test("can build a tier with all parameters", function (): void {
        $builder = new TierBuilder();
        $tier = $builder->build(
            upTo: 5000,
            unitAmount: 80,
            unitAmountDecimal: "0.80",
            flatAmount: 500,
            flatAmountDecimal: "5.00"
        );

        expect($tier)
            ->toBeInstanceOf(StripeProductTier::class)
            ->and($tier->upTo)->toBe(5000)
            ->and($tier->unitAmount)->toBe(80)
            ->and($tier->unitAmountDecimal)->toBe("0.80")
            ->and($tier->flatAmount)->toBe(500)
            ->and($tier->flatAmountDecimal)->toBe("5.00");
    });

    test("can build infinite tier", function (): void {
        $builder = new TierBuilder();
        $tier = $builder->build(
            upTo: "inf",
            unitAmount: 60
        );

        expect($tier)
            ->toBeInstanceOf(StripeProductTier::class)
            ->and($tier->upTo)->toBe("inf")
            ->and($tier->unitAmount)->toBe(60);
    });

    test("can build tier with flat amount only", function (): void {
        $builder = new TierBuilder();
        $tier = $builder->build(
            upTo: 100,
            flatAmount: 1000,
            unitAmount: 0
        );

        expect($tier)
            ->toBeInstanceOf(StripeProductTier::class)
            ->and($tier->upTo)->toBe(100)
            ->and($tier->flatAmount)->toBe(1000)
            ->and($tier->unitAmount)->toBe(0);
    });
});