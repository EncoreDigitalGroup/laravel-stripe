<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeFinancialConnection;
use EncoreDigitalGroup\Stripe\Objects\Product\StripePrice;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeProduct;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeWebhook;
use EncoreDigitalGroup\Stripe\Stripe;
use Stripe\Util\Util;

describe("Internal Builder Usage Integration", function (): void {
    test("Stripe facade methods use builders internally", function (): void {
        // Test that the facade methods are using builders internally
        // This verifies that our refactoring was successful

        $customer = Stripe::customer(
            email: "test@example.com",
            name: "John Doe"
        );

        expect($customer)
            ->toBeInstanceOf(StripeCustomer::class)
            ->and($customer->email)->toBe("test@example.com")
            ->and($customer->name)->toBe("John Doe");

        $address = Stripe::address(
            line1: "123 Main St",
            city: "Boston",
            country: "US"
        );

        expect($address)
            ->toBeInstanceOf(StripeAddress::class)
            ->and($address->line1)->toBe("123 Main St")
            ->and($address->city)->toBe("Boston")
            ->and($address->country)->toBe("US");
    });

    test("DTO fromStripeObject methods use builders for nested objects", function (): void {
        // Create a mock Stripe customer object with address and shipping
        $stripeCustomer = Util::convertToStripeObject([
            'id' => 'cus_123',
            'object' => 'customer',
            'email' => 'customer@example.com',
            'name' => 'Jane Smith',
            'address' => [
                'line1' => '456 Oak Ave',
                'city' => 'Cambridge',
                'state' => 'MA',
                'postal_code' => '02138',
                'country' => 'US'
            ],
            'shipping' => [
                'name' => 'Jane Smith',
                'phone' => '+1234567890',
                'address' => [
                    'line1' => '789 Pine St',
                    'city' => 'Boston',
                    'state' => 'MA',
                    'postal_code' => '02101',
                    'country' => 'US'
                ]
            ]
        ], []);

        // Convert from Stripe object - this should use builders internally
        $customer = StripeCustomer::fromStripeObject($stripeCustomer);

        expect($customer)
            ->toBeInstanceOf(StripeCustomer::class)
            ->and($customer->id)->toBe('cus_123')
            ->and($customer->email)->toBe('customer@example.com')
            ->and($customer->name)->toBe('Jane Smith')
            ->and($customer->address)->toBeInstanceOf(StripeAddress::class)
            ->and($customer->address->line1)->toBe('456 Oak Ave')
            ->and($customer->address->city)->toBe('Cambridge')
            ->and($customer->shipping)->not->toBeNull()
            ->and($customer->shipping->name)->toBe('Jane Smith')
            ->and($customer->shipping->address->line1)->toBe('789 Pine St');
    });

    test("StripePrice fromStripeObject uses builders for complex nested objects", function (): void {
        // Create a mock Stripe price object with recurring and tiers
        $stripePrice = Util::convertToStripeObject([
            'id' => 'price_123',
            'object' => 'price',
            'product' => 'prod_123',
            'currency' => 'usd',
            'recurring' => [
                'interval' => 'month',
                'interval_count' => 1,
                'trial_period_days' => 14,
                'usage_type' => 'licensed'
            ],
            'tiers' => [
                [
                    'up_to' => 1000,
                    'unit_amount' => 100,
                    'flat_amount' => 0
                ],
                [
                    'up_to' => 'inf',
                    'unit_amount' => 80,
                    'flat_amount' => 0
                ]
            ],
            'custom_unit_amount' => [
                'minimum' => 500,
                'maximum' => 10000,
                'preset' => 2000
            ],
            'metadata' => []
        ], []);

        // Convert from Stripe object - this should use builders internally
        $price = StripePrice::fromStripeObject($stripePrice);

        expect($price)
            ->toBeInstanceOf(StripePrice::class)
            ->and($price->id)->toBe('price_123')
            ->and($price->product)->toBe('prod_123')
            ->and($price->recurring)->not->toBeNull()
            ->and($price->recurring->interval->value)->toBe('month')
            ->and($price->recurring->intervalCount)->toBe(1)
            ->and($price->recurring->trialPeriodDays)->toBe(14)
            ->and($price->tiers)->toHaveCount(2)
            ->and($price->tiers->first()->upTo)->toBe(1000)
            ->and($price->tiers->first()->unitAmount)->toBe(100)
            ->and($price->tiers->last()->upTo)->toBe('inf')
            ->and($price->tiers->last()->unitAmount)->toBe(80)
            ->and($price->customUnitAmount)->not->toBeNull()
            ->and($price->customUnitAmount->minimum)->toBe(500)
            ->and($price->customUnitAmount->maximum)->toBe(10000)
            ->and($price->customUnitAmount->preset)->toBe(2000);
    });

    test("StripePrice make method uses builders for array parameters", function (): void {
        // Test that the make method converts array parameters using builders
        $price = StripePrice::make(
            product: 'prod_123',
            currency: 'usd',
            unitAmount: 2999,
            customUnitAmount: [
                'minimum' => 1000,
                'maximum' => 50000,
                'preset' => 5000
            ]
        );

        expect($price)
            ->toBeInstanceOf(StripePrice::class)
            ->and($price->product)->toBe('prod_123')
            ->and($price->currency)->toBe('usd')
            ->and($price->unitAmount)->toBe(2999)
            ->and($price->customUnitAmount)->not->toBeNull()
            ->and($price->customUnitAmount->minimum)->toBe(1000)
            ->and($price->customUnitAmount->maximum)->toBe(50000)
            ->and($price->customUnitAmount->preset)->toBe(5000);
    });

    test("all facade methods consistently use builder pattern", function (): void {
        // Verify that all facade methods are using the builder pattern
        $product = Stripe::product(name: "Test Product");
        $price = Stripe::price(product: "prod_123", currency: "usd", unitAmount: 1999);
        $subscription = Stripe::subscription(customer: "cus_123");
        $webhook = Stripe::webhook(url: "https://example.com/webhook", events: ["*"]);
        $financialConnection = Stripe::financialConnections(customer: Stripe::customer(email: "test@example.com"));

        // All should be proper object instances
        expect($product)->toBeInstanceOf(StripeProduct::class)
            ->and($price)->toBeInstanceOf(StripePrice::class)
            ->and($subscription)->toBeInstanceOf(StripeSubscription::class)
            ->and($webhook)->toBeInstanceOf(StripeWebhook::class)
            ->and($financialConnection)->toBeInstanceOf(StripeFinancialConnection::class)
            ->and($product->name)->toBe("Test Product")
            ->and($price->product)->toBe("prod_123")
            ->and($price->currency)->toBe("usd")
            ->and($price->unitAmount)->toBe(1999)
            ->and($subscription->customer)->toBe("cus_123")
            ->and($webhook->url)->toBe("https://example.com/webhook")
            ->and($webhook->events)->toBe(["*"]);
    });
});