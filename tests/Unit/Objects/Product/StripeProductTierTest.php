<?php

use EncoreDigitalGroup\Stripe\Objects\Product\StripeProductTier;

describe("StripeProductTier", function (): void {
    test("can create tier using make method", function (): void {
        $tier = StripeProductTier::make(
            upTo: 100,
            unitAmount: 1000,
            unitAmountDecimal: "10.00",
            flatAmount: 500,
            flatAmountDecimal: "5.00"
        );

        expect($tier)->toBeInstanceOf(StripeProductTier::class)
            ->and($tier->upTo())->toBe(100)
            ->and($tier->unitAmount())->toBe(1000)
            ->and($tier->unitAmountDecimal())->toBe("10.00")
            ->and($tier->flatAmount())->toBe(500)
            ->and($tier->flatAmountDecimal())->toBe("5.00");
    });

    test("can create tier with inf upTo value", function (): void {
        $tier = StripeProductTier::make(
            upTo: "inf",
            unitAmount: 1000
        );

        expect($tier->upTo())->toBe("inf")
            ->and($tier->unitAmount())->toBe(1000);
    });

    test("toArray returns correct structure", function (): void {
        $tier = StripeProductTier::make(
            upTo: 100,
            unitAmount: 1000,
            flatAmount: 500
        );

        $array = $tier->toArray();

        expect($array)->toBe([
            "up_to" => 100,
            "unit_amount" => 1000,
            "flat_amount" => 500,
        ]);
    });

    test("toArray filters null values", function (): void {
        $tier = StripeProductTier::make(
            upTo: 100,
            unitAmount: 1000
        );

        $array = $tier->toArray();

        expect($array)->not->toHaveKey("unit_amount_decimal")
            ->and($array)->not->toHaveKey("flat_amount")
            ->and($array)->not->toHaveKey("flat_amount_decimal");
    });

    test("withUpTo sets and returns self", function (): void {
        $tier = StripeProductTier::make();
        $result = $tier->withUpTo(200);

        expect($result)->toBe($tier)
            ->and($tier->upTo())->toBe(200);
    });

    test("withUnitAmount sets and returns self", function (): void {
        $tier = StripeProductTier::make();
        $result = $tier->withUnitAmount(1500);

        expect($result)->toBe($tier)
            ->and($tier->unitAmount())->toBe(1500);
    });

    test("withUnitAmountDecimal sets and returns self", function (): void {
        $tier = StripeProductTier::make();
        $result = $tier->withUnitAmountDecimal("15.50");

        expect($result)->toBe($tier)
            ->and($tier->unitAmountDecimal())->toBe("15.50");
    });

    test("withFlatAmount sets and returns self", function (): void {
        $tier = StripeProductTier::make();
        $result = $tier->withFlatAmount(750);

        expect($result)->toBe($tier)
            ->and($tier->flatAmount())->toBe(750);
    });

    test("withFlatAmountDecimal sets and returns self", function (): void {
        $tier = StripeProductTier::make();
        $result = $tier->withFlatAmountDecimal("7.50");

        expect($result)->toBe($tier)
            ->and($tier->flatAmountDecimal())->toBe("7.50");
    });

    test("fluent setters can be chained", function (): void {
        $tier = StripeProductTier::make()
            ->withUpTo(100)
            ->withUnitAmount(1000)
            ->withFlatAmount(500);

        expect($tier->upTo())->toBe(100)
            ->and($tier->unitAmount())->toBe(1000)
            ->and($tier->flatAmount())->toBe(500);
    });

    test("getters return null for unset values", function (): void {
        $tier = StripeProductTier::make();

        expect($tier->upTo())->toBeNull()
            ->and($tier->unitAmount())->toBeNull()
            ->and($tier->unitAmountDecimal())->toBeNull()
            ->and($tier->flatAmount())->toBeNull()
            ->and($tier->flatAmountDecimal())->toBeNull();
    });
});
