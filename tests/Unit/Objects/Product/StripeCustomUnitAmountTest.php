<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Product\StripeCustomUnitAmount;

describe("StripeCustomUnitAmount", function (): void {
    test("can create StripeCustomUnitAmount using make method", function (): void {
        $customUnitAmount = StripeCustomUnitAmount::make(
            minimum: 500,
            maximum: 10000,
            preset: 2000
        );

        expect($customUnitAmount)
            ->toBeInstanceOf(StripeCustomUnitAmount::class)
            ->and($customUnitAmount->minimum())->toBe(500)
            ->and($customUnitAmount->maximum())->toBe(10000)
            ->and($customUnitAmount->preset())->toBe(2000);
    });

    test("can create StripeCustomUnitAmount with partial parameters", function (): void {
        $customUnitAmount = StripeCustomUnitAmount::make(
            minimum: 100
        );

        expect($customUnitAmount)
            ->toBeInstanceOf(StripeCustomUnitAmount::class)
            ->and($customUnitAmount->minimum())->toBe(100)
            ->and($customUnitAmount->maximum())->toBeNull()
            ->and($customUnitAmount->preset())->toBeNull();
    });

    test("can create StripeCustomUnitAmount with no parameters", function (): void {
        $customUnitAmount = StripeCustomUnitAmount::make();

        expect($customUnitAmount)
            ->toBeInstanceOf(StripeCustomUnitAmount::class)
            ->and($customUnitAmount->minimum())->toBeNull()
            ->and($customUnitAmount->maximum())->toBeNull()
            ->and($customUnitAmount->preset())->toBeNull();
    });

    test("toArray returns correct structure", function (): void {
        $customUnitAmount = StripeCustomUnitAmount::make(
            minimum: 500,
            maximum: 10000,
            preset: 2000
        );

        $array = $customUnitAmount->toArray();

        expect($array)
            ->toBeArray()
            ->and($array["minimum"])->toBe(500)
            ->and($array["maximum"])->toBe(10000)
            ->and($array["preset"])->toBe(2000);
    });

    test("toArray filters null values", function (): void {
        $customUnitAmount = StripeCustomUnitAmount::make(
            minimum: 500
        );

        $array = $customUnitAmount->toArray();

        expect($array)
            ->toBeArray()
            ->and($array)->toHaveKey("minimum")
            ->and($array["minimum"])->toBe(500)
            ->and($array)->not->toHaveKey("maximum")
            ->and($array)->not->toHaveKey("preset");
    });
});