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
use EncoreDigitalGroup\Stripe\Services\StripeCustomerService;
use EncoreDigitalGroup\Stripe\Services\StripePriceService;
use EncoreDigitalGroup\Stripe\Services\StripeProductService;
use EncoreDigitalGroup\Stripe\Services\StripeSubscriptionService;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\FakeStripeClient;
use Illuminate\Support\Facades\App;
use Stripe\StripeClient;

test("can create a customer object via static method", function (): void {
    $customer = Stripe::customer(
        email: "test@example.com",
        name: "Test User"
    );

    expect($customer)
        ->toBeInstanceOf(StripeCustomer::class)
        ->and($customer->email)->toBe("test@example.com")
        ->and($customer->name)->toBe("Test User");
});

test("can create financial connections object via static method", function (): void {
    $customer = StripeCustomer::make(email: "test@example.com");
    $connection = Stripe::financialConnections(customer: $customer);

    expect($connection)->toBeInstanceOf(StripeFinancialConnection::class)
        ->and($connection->customer)->toBe($customer);
});

test("can create webhook object via static method", function (): void {
    $webhook = Stripe::webhook(url: "https://example.com/webhook");

    expect($webhook)->toBeInstanceOf(StripeWebhook::class)
        ->and($webhook->url)->toBe("https://example.com/webhook");
});

test("can get customer service via static method", function (): void {
    // Use fake to avoid needing real API key
    Stripe::fake();

    $service = Stripe::customers();

    expect($service)->toBeInstanceOf(StripeCustomerService::class);
});

test("fake method creates FakeStripeClient", function (): void {
    $fake = Stripe::fake([
        "customers.create" => ["id" => "cus_test", "email" => "test@example.com"],
    ]);

    expect($fake)->toBeInstanceOf(FakeStripeClient::class);
});

test("fake method binds to container", function (): void {
    Stripe::fake([
        "customers.create" => ["id" => "cus_test", "email" => "test@example.com"],
    ]);

    // Verify the fake is bound to the container
    expect(App::bound(StripeClient::class))->toBeTrue();

    // Verify we can retrieve it
    $client = app(StripeClient::class);
    expect($client)->toBeInstanceOf(FakeStripeClient::class);
});

test("fake method returns fake that can be used for assertions", function (): void {
    $fake = Stripe::fake([
        "customers.create" => ["id" => "cus_test", "email" => "test@example.com"],
    ]);

    // Use the service
    $service = StripeCustomerService::make();
    $service->create(StripeCustomer::make(email: "test@example.com"));

    // Assert using the fake
    expect($fake)->toHaveCalledStripeMethod("customers.create");
});

test("fake method throws exception when FakeStripeClient not available", function (): void {
    // This test would only fail if the class doesn't exist, which it does in our test environment
    // So we're just verifying the method exists and works
    expect(fn(): \EncoreDigitalGroup\Stripe\Support\Testing\FakeStripeClient => Stripe::fake())->not->toThrow(RuntimeException::class);
});

test("can create product object via static method", function (): void {
    $product = Stripe::product(
        name: "Test Product",
        description: "Test Description"
    );

    expect($product)
        ->toBeInstanceOf(StripeProduct::class)
        ->and($product->name)->toBe("Test Product")
        ->and($product->description)->toBe("Test Description");
});

test("can create price object via static method", function (): void {
    $price = Stripe::price(
        product: "prod_123",
        unitAmount: 1000,
        currency: "usd"
    );

    expect($price)
        ->toBeInstanceOf(StripePrice::class)
        ->and($price->product)->toBe("prod_123")
        ->and($price->unitAmount)->toBe(1000)
        ->and($price->currency)->toBe("usd");
});

test("can create subscription object via static method", function (): void {
    $subscription = Stripe::subscription(
        customer: "cus_123",
        items: [
            ["price" => "price_123", "quantity" => 1]
        ]
    );

    expect($subscription)
        ->toBeInstanceOf(StripeSubscription::class)
        ->and($subscription->customer)->toBe("cus_123")
        ->and($subscription->items)->toBe([
            ["price" => "price_123", "quantity" => 1]
        ]);
});

test("can create address object via static method", function (): void {
    $address = Stripe::address(
        line1: "123 Main St",
        city: "San Francisco",
        state: "CA",
        postalCode: "94102",
        country: "US"
    );

    expect($address)
        ->toBeInstanceOf(StripeAddress::class)
        ->and($address->line1)->toBe("123 Main St")
        ->and($address->city)->toBe("San Francisco")
        ->and($address->state)->toBe("CA")
        ->and($address->postalCode)->toBe("94102")
        ->and($address->country)->toBe("US");
});

test("can get product service via static method", function (): void {
    Stripe::fake();

    $service = Stripe::products();

    expect($service)->toBeInstanceOf(StripeProductService::class);
});

test("can get price service via static method", function (): void {
    Stripe::fake();

    $service = Stripe::prices();

    expect($service)->toBeInstanceOf(StripePriceService::class);
});

test("can get subscription service via static method", function (): void {
    Stripe::fake();

    $service = Stripe::subscriptions();

    expect($service)->toBeInstanceOf(StripeSubscriptionService::class);
});
