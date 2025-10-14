<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Enums\RecurringInterval;
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeCustomUnitAmount;
use EncoreDigitalGroup\Stripe\Objects\Product\StripePrice;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeProduct;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeProductTier;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeRecurring;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeWebhook;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Building\StripeBuilder;

describe("Builder Pattern Integration", function (): void {
    test("can access main builder from Stripe facade", function (): void {
        $builder = Stripe::builder();

        expect($builder)->toBeInstanceOf(StripeBuilder::class);
    });

    test("can build customer with nested address using builder pattern", function (): void {
        $customer = Stripe::builder()->customer()->build(
            email: "user@example.com",
            name: "John Doe",
            address: Stripe::builder()->address()->build(
                line1: "123 Main St",
                city: "Boston",
                state: "MA",
                postalCode: "02101",
                country: "US"
            )
        );

        expect($customer)
            ->toBeInstanceOf(StripeCustomer::class)
            ->and($customer->email)->toBe("user@example.com")
            ->and($customer->name)->toBe("John Doe")
            ->and($customer->address)->toBeInstanceOf(StripeAddress::class)
            ->and($customer->address->line1)->toBe("123 Main St")
            ->and($customer->address->city)->toBe("Boston");
    });

    test("can build price with recurring using builder pattern", function (): void {
        $price = Stripe::builder()->price()->build(
            product: "prod_123",
            unitAmount: 2999,
            currency: "usd",
            recurring: Stripe::builder()->price()->recurring()->build(
                interval: RecurringInterval::Month,
                intervalCount: 1
            )
        );

        expect($price)
            ->toBeInstanceOf(StripePrice::class)
            ->and($price->product)->toBe("prod_123")
            ->and($price->unitAmount)->toBe(2999)
            ->and($price->currency)->toBe("usd")
            ->and($price->recurring)->toBeInstanceOf(StripeRecurring::class)
            ->and($price->recurring->interval)->toBe(RecurringInterval::Month)
            ->and($price->recurring->intervalCount)->toBe(1);
    });

    test("can build price with tiers using builder pattern", function (): void {
        $price = Stripe::builder()->price()->build(
            product: "prod_123",
            currency: "usd",
            tiers: [
                Stripe::builder()->price()->tier()->build(
                    upTo: 1000,
                    unitAmount: 100
                ),
                Stripe::builder()->price()->tier()->build(
                    upTo: "inf",
                    unitAmount: 80
                )
            ]
        );

        expect($price)
            ->toBeInstanceOf(StripePrice::class)
            ->and($price->tiers)->toHaveCount(2)
            ->and($price->tiers->first())->toBeInstanceOf(StripeProductTier::class)
            ->and($price->tiers->first()->upTo)->toBe(1000)
            ->and($price->tiers->first()->unitAmount)->toBe(100)
            ->and($price->tiers->last())->toBeInstanceOf(StripeProductTier::class)
            ->and($price->tiers->last()->upTo)->toBe("inf")
            ->and($price->tiers->last()->unitAmount)->toBe(80);
    });

    test("can build price with custom unit amount using builder pattern", function (): void {
        $price = Stripe::builder()->price()->build(
            product: "prod_123",
            currency: "usd",
            customUnitAmount: Stripe::builder()->price()->customUnitAmount()->build(
                minimum: 500,
                maximum: 100000,
                preset: 2000
            )
        );

        expect($price)
            ->toBeInstanceOf(StripePrice::class)
            ->and($price->customUnitAmount)->toBeInstanceOf(StripeCustomUnitAmount::class)
            ->and($price->customUnitAmount->minimum)->toBe(500)
            ->and($price->customUnitAmount->maximum)->toBe(100000)
            ->and($price->customUnitAmount->preset)->toBe(2000);
    });

    test("can build webhook using builder pattern", function (): void {
        $webhook = Stripe::builder()->webhook()->build(
            url: "https://api.example.com/webhooks/stripe",
            events: ["customer.created", "payment_intent.succeeded"]
        );

        expect($webhook)
            ->toBeInstanceOf(StripeWebhook::class)
            ->and($webhook->url)->toBe("https://api.example.com/webhooks/stripe")
            ->and($webhook->events)->toBe(["customer.created", "payment_intent.succeeded"]);
    });

    test("can build product using builder pattern", function (): void {
        $product = Stripe::builder()->product()->build(
            name: "Test Product",
            description: "A test product created with builder pattern",
            active: true,
            metadata: ["category" => "test"]
        );

        expect($product)
            ->toBeInstanceOf(StripeProduct::class)
            ->and($product->name)->toBe("Test Product")
            ->and($product->description)->toBe("A test product created with builder pattern")
            ->and($product->active)->toBeTrue()
            ->and($product->metadata)->toBe(["category" => "test"]);
    });

    test("builder pattern provides same result as direct DTO creation", function (): void {
        // Create using builder pattern
        $builderTier = Stripe::builder()->price()->tier()->build(
            upTo: 1000,
            unitAmount: 100
        );

        // Create using direct DTO
        $directTier = StripeProductTier::make(
            upTo: 1000,
            unitAmount: 100
        );

        expect($builderTier)
            ->toBeInstanceOf(StripeProductTier::class)
            ->and($builderTier->upTo)->toBe($directTier->upTo)
            ->and($builderTier->unitAmount)->toBe($directTier->unitAmount);
    });

    test("builder pattern provides same result as facade shortcuts", function (): void {
        // Create using builder pattern
        $builderProduct = Stripe::builder()->product()->build(
            name: "Test Product",
            active: true
        );

        // Create using facade shortcut
        $facadeProduct = Stripe::product(
            name: "Test Product",
            active: true
        );

        expect($builderProduct)
            ->toBeInstanceOf(StripeProduct::class)
            ->and($builderProduct->name)->toBe($facadeProduct->name)
            ->and($builderProduct->active)->toBe($facadeProduct->active);
    });
});