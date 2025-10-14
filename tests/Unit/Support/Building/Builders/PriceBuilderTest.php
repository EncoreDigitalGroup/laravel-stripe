<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Enums\BillingScheme;
use EncoreDigitalGroup\Stripe\Enums\PriceType;
use EncoreDigitalGroup\Stripe\Enums\TaxBehavior;
use EncoreDigitalGroup\Stripe\Objects\Product\StripePrice;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\CustomUnitAmountBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\PriceBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\RecurringBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\TierBuilder;

describe("PriceBuilder", function (): void {
    test("can build a basic price", function (): void {
        $builder = new PriceBuilder();
        $price = $builder->build(
            product: "prod_123",
            unitAmount: 2999,
            currency: "usd"
        );

        expect($price)
            ->toBeInstanceOf(StripePrice::class)
            ->and($price->product)->toBe("prod_123")
            ->and($price->unitAmount)->toBe(2999)
            ->and($price->currency)->toBe("usd");
    });

    test("can build a price with all parameters", function (): void {
        $builder = new PriceBuilder();
        $price = $builder->build(
            id: "price_123",
            product: "prod_123",
            active: true,
            currency: "usd",
            unitAmount: 2999,
            unitAmountDecimal: "29.99",
            type: PriceType::OneTime,
            billingScheme: BillingScheme::PerUnit,
            nickname: "Premium Plan",
            metadata: ["tier" => "premium"],
            lookupKey: "premium_plan",
            transformQuantity: 1,
            taxBehavior: TaxBehavior::Exclusive,
            created: 1640995200
        );

        expect($price)
            ->toBeInstanceOf(StripePrice::class)
            ->and($price->id)->toBe("price_123")
            ->and($price->product)->toBe("prod_123")
            ->and($price->active)->toBeTrue()
            ->and($price->currency)->toBe("usd")
            ->and($price->unitAmount)->toBe(2999)
            ->and($price->unitAmountDecimal)->toBe("29.99")
            ->and($price->type)->toBe(PriceType::OneTime)
            ->and($price->billingScheme)->toBe(BillingScheme::PerUnit)
            ->and($price->nickname)->toBe("Premium Plan")
            ->and($price->metadata)->toBe(["tier" => "premium"])
            ->and($price->lookupKey)->toBe("premium_plan")
            ->and($price->transformQuantity)->toBe(1)
            ->and($price->taxBehavior)->toBe(TaxBehavior::Exclusive)
            ->and($price->created)->toBe(1640995200);
    });

    describe("Nested Builders", function (): void {
        test("can access tier builder", function (): void {
            $builder = new PriceBuilder();
            $tierBuilder = $builder->tier();

            expect($tierBuilder)->toBeInstanceOf(TierBuilder::class);
        });

        test("can access custom unit amount builder", function (): void {
            $builder = new PriceBuilder();
            $customUnitAmountBuilder = $builder->customUnitAmount();

            expect($customUnitAmountBuilder)->toBeInstanceOf(CustomUnitAmountBuilder::class);
        });

        test("can access recurring builder", function (): void {
            $builder = new PriceBuilder();
            $recurringBuilder = $builder->recurring();

            expect($recurringBuilder)->toBeInstanceOf(RecurringBuilder::class);
        });
    });
});