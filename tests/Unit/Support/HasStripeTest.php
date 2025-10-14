<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Services\StripeCustomerService;
use EncoreDigitalGroup\Stripe\Support\Config\StripeConfig;
use EncoreDigitalGroup\Stripe\Support\Testing\FakeStripeClient;
use Stripe\StripeClient;

test("HasStripe accepts injected client", function (): void {
    $mockClient = new FakeStripeClient();

    $service = new StripeCustomerService($mockClient);

    expect($service)->toBeInstanceOf(StripeCustomerService::class);
});

test("HasStripe resolves from container when bound", function (): void {
    // Bind a fake client to container
    $fakeClient = new FakeStripeClient();
    app()->instance(StripeClient::class, $fakeClient);

    // Create service without injection
    $service = new StripeCustomerService();

    expect($service)->toBeInstanceOf(StripeCustomerService::class);
});

test("HasStripe throws exception when no API key configured", function (): void {
    // Ensure nothing is bound
    if (app()->bound(StripeClient::class)) {
        app()->forgetInstance(StripeClient::class);
    }

    // Clear any config
    config(['stripe.secret_key' => null]);

    expect(fn() => new StripeCustomerService())
        ->toThrow(\EncoreDigitalGroup\StdLib\Exceptions\NullExceptions\ClassPropertyNullException::class);
});

test("HasStripe make method creates new instance with injected client", function (): void {
    $fakeClient = new FakeStripeClient();

    $service = StripeCustomerService::make($fakeClient);

    expect($service)->toBeInstanceOf(StripeCustomerService::class);
});

test("HasStripe make method accepts client parameter", function (): void {
    $fakeClient = new FakeStripeClient();

    $service = StripeCustomerService::make($fakeClient);

    expect($service)->toBeInstanceOf(StripeCustomerService::class);
});

test("HasStripe config method returns StripeConfig", function (): void {
    $config = StripeCustomerService::config();

    expect($config)->toBeInstanceOf(StripeConfig::class);
});

test("HasStripe client method returns StripeClient", function (): void {
    // Use fake to avoid needing real API key
    $fakeClient = new FakeStripeClient();
    app()->instance(StripeClient::class, $fakeClient);

    $client = StripeCustomerService::client();

    expect($client)->toBeInstanceOf(StripeClient::class);
});