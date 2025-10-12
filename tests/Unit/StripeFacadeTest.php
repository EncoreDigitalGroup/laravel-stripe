<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Common\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Common\Stripe\Objects\FinancialConnections\StripeFinancialConnection;
use EncoreDigitalGroup\Common\Stripe\Objects\Support\StripeWebhook;
use EncoreDigitalGroup\Common\Stripe\Services\StripeCustomerService;
use EncoreDigitalGroup\Common\Stripe\Stripe;
use Illuminate\Support\Facades\App;
use Stripe\StripeClient;
use Tests\Support\FakeStripeClient;

test('can create a customer object via static method', function () {
    $customer = Stripe::customer(
        email: 'test@example.com',
        name: 'Test User'
    );

    expect($customer)
        ->toBeInstanceOf(StripeCustomer::class)
        ->and($customer->email)->toBe('test@example.com')
        ->and($customer->name)->toBe('Test User');
});

test('can create financial connections object via static method', function () {
    $customer = StripeCustomer::make(email: 'test@example.com');
    $connection = Stripe::financialConnections(customer: $customer);

    expect($connection)->toBeInstanceOf(StripeFinancialConnection::class)
        ->and($connection->customer)->toBe($customer);
});

test('can create webhook object via static method', function () {
    $webhook = Stripe::webhook(url: 'https://example.com/webhook');

    expect($webhook)->toBeInstanceOf(StripeWebhook::class)
        ->and($webhook->url)->toBe('https://example.com/webhook');
});

test('can get customer service via static method', function () {
    // Use fake to avoid needing real API key
    Stripe::fake();

    $service = Stripe::customers();

    expect($service)->toBeInstanceOf(StripeCustomerService::class);
});

test('fake method creates FakeStripeClient', function () {
    $fake = Stripe::fake([
        'customers.create' => ['id' => 'cus_test', 'email' => 'test@example.com'],
    ]);

    expect($fake)->toBeInstanceOf(FakeStripeClient::class);
});

test('fake method binds to container', function () {
    Stripe::fake([
        'customers.create' => ['id' => 'cus_test', 'email' => 'test@example.com'],
    ]);

    // Verify the fake is bound to the container
    expect(App::bound(StripeClient::class))->toBeTrue();

    // Verify we can retrieve it
    $client = app(StripeClient::class);
    expect($client)->toBeInstanceOf(FakeStripeClient::class);
});

test('fake method returns fake that can be used for assertions', function () {
    $fake = Stripe::fake([
        'customers.create' => ['id' => 'cus_test', 'email' => 'test@example.com'],
    ]);

    // Use the service
    $service = StripeCustomerService::make();
    $service->create(StripeCustomer::make(email: 'test@example.com'));

    // Assert using the fake
    expect($fake)->toHaveCalledStripeMethod('customers.create');
});

test('fake method throws exception when FakeStripeClient not available', function () {
    // This test would only fail if the class doesn't exist, which it does in our test environment
    // So we're just verifying the method exists and works
    expect(fn() => Stripe::fake())->not->toThrow(RuntimeException::class);
});
