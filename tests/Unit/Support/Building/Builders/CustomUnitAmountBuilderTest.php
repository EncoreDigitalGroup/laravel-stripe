<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Product\StripeCustomUnitAmount;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\CustomUnitAmountBuilder;

describe("CustomUnitAmountBuilder", function (): void {
    test("can build a basic custom unit amount", function (): void {
        $builder = new CustomUnitAmountBuilder;
        $customUnitAmount = $builder->build(
            minimum: 500
        );

        expect($customUnitAmount)
            ->toBeInstanceOf(StripeCustomUnitAmount::class)
            ->and($customUnitAmount->minimum)->toBe(500);
    });

    test("can build custom unit amount with all parameters", function (): void {
        $builder = new CustomUnitAmountBuilder;
        $customUnitAmount = $builder->build(
            minimum: 500,
            maximum: 100000,
            preset: 2000
        );

        expect($customUnitAmount)
            ->toBeInstanceOf(StripeCustomUnitAmount::class)
            ->and($customUnitAmount->minimum)->toBe(500)
            ->and($customUnitAmount->maximum)->toBe(100000)
            ->and($customUnitAmount->preset)->toBe(2000);
    });

    test("can build custom unit amount with only maximum", function (): void {
        $builder = new CustomUnitAmountBuilder;
        $customUnitAmount = $builder->build(
            maximum: 50000
        );

        expect($customUnitAmount)
            ->toBeInstanceOf(StripeCustomUnitAmount::class)
            ->and($customUnitAmount->minimum)->toBeNull()
            ->and($customUnitAmount->maximum)->toBe(50000)
            ->and($customUnitAmount->preset)->toBeNull();
    });

    test("can build custom unit amount with preset only", function (): void {
        $builder = new CustomUnitAmountBuilder;
        $customUnitAmount = $builder->build(
            preset: 1500
        );

        expect($customUnitAmount)
            ->toBeInstanceOf(StripeCustomUnitAmount::class)
            ->and($customUnitAmount->minimum)->toBeNull()
            ->and($customUnitAmount->maximum)->toBeNull()
            ->and($customUnitAmount->preset)->toBe(1500);
    });
});