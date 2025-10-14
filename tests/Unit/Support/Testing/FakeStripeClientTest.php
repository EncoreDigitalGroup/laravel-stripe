<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Support\Testing\FakeStripeClient;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

test("can add fakes dynamically with fake method", function (): void {
    $client = new FakeStripeClient();

    $client->fake("customers.create", ["id" => "cus_dynamic"]);

    $service = $client->customers;
    $result = $service->create(["email" => "test@example.com"]);

    expect($result->id)->toBe("cus_dynamic");
});

test("can add multiple fakes with fakeMany", function (): void {
    $client = new FakeStripeClient();

    $client->fakeMany([
        "customers.create" => ["id" => "cus_1"],
        "products.create" => ["id" => "prod_1"]
    ]);

    $customerResult = $client->customers->create(["email" => "test@example.com"]);
    $productResult = $client->products->create(["name" => "Test"]);

    expect($customerResult->id)->toBe("cus_1")
        ->and($productResult->id)->toBe("prod_1");
});

test("can check if method was called", function (): void {
    $client = new FakeStripeClient([
        "customers.create" => ["id" => "cus_test"]
    ]);

    expect($client->wasCalled("customers.create"))->toBeFalse();

    $client->customers->create(["email" => "test@example.com"]);

    expect($client->wasCalled("customers.create"))->toBeTrue();
});

test("can get call count for method", function (): void {
    $client = new FakeStripeClient([
        "customers.create" => ["id" => "cus_test"]
    ]);

    expect($client->callCount("customers.create"))->toBe(0);

    $client->customers->create(["email" => "test1@example.com"]);
    $client->customers->create(["email" => "test2@example.com"]);

    expect($client->callCount("customers.create"))->toBe(2);
});

test("can get specific call parameters", function (): void {
    $client = new FakeStripeClient([
        "customers.create" => ["id" => "cus_test"]
    ]);

    $client->customers->create(["email" => "first@example.com"]);
    $client->customers->create(["email" => "second@example.com"]);

    $firstCall = $client->getCall("customers.create", 0);
    $secondCall = $client->getCall("customers.create", 1);

    expect($firstCall)->toBe(["email" => "first@example.com"])
        ->and($secondCall)->toBe(["email" => "second@example.com"]);
});

test("getCall returns null for non-existent call", function (): void {
    $client = new FakeStripeClient([
        "customers.create" => ["id" => "cus_test"]
    ]);

    $call = $client->getCall("customers.create", 5);

    expect($call)->toBeNull();
});

test("can clear recorded calls", function (): void {
    $client = new FakeStripeClient([
        "customers.create" => ["id" => "cus_test"]
    ]);

    $client->customers->create(["email" => "test@example.com"]);
    expect($client->callCount("customers.create"))->toBe(1);

    $client->clearRecorded();

    expect($client->callCount("customers.create"))->toBe(0);
});

test("accepts BackedEnum as fake key in constructor", function (): void {
    $client = new FakeStripeClient([
        StripeMethod::CustomersCreate->value => ["id" => "cus_enum"]
    ]);

    $result = $client->customers->create(["email" => "test@example.com"]);

    expect($result->id)->toBe("cus_enum");
});

test("fake method accepts BackedEnum", function (): void {
    $client = new FakeStripeClient();

    $client->fake(StripeMethod::CustomersCreate, ["id" => "cus_enum_dynamic"]);

    $result = $client->customers->create(["email" => "test@example.com"]);

    expect($result->id)->toBe("cus_enum_dynamic");
});

test("handles wildcard patterns", function (): void {
    $client = new FakeStripeClient([
        "customers.*" => ["id" => "cus_wildcard"]
    ]);

    $createResult = $client->customers->create(["email" => "test@example.com"]);
    $retrieveResult = $client->customers->retrieve("cus_123");

    expect($createResult->id)->toBe("cus_wildcard")
        ->and($retrieveResult->id)->toBe("cus_wildcard");
});

test("callable responses receive params", function (): void {
    $client = new FakeStripeClient([
        "customers.create" => function ($params) {
            return [
                "id" => "cus_callable",
                "email" => $params["email"]
            ];
        }
    ]);

    $result = $client->customers->create(["email" => "dynamic@example.com"]);

    expect($result->email)->toBe("dynamic@example.com");
});

test("throws exception when no fake registered", function (): void {
    $client = new FakeStripeClient();

    expect(fn() => $client->customers->create(["email" => "test@example.com"]))
        ->toThrow(RuntimeException::class, "No fake registered for Stripe method [customers.create]");
});

test("handles method calls with id parameter", function (): void {
    $client = new FakeStripeClient([
        "customers.retrieve" => ["id" => "cus_retrieved"]
    ]);

    $result = $client->customers->retrieve("cus_123");

    expect($result->id)->toBe("cus_retrieved");
});

test("records call with id and params separately", function (): void {
    $client = new FakeStripeClient([
        "customers.update" => ["id" => "cus_updated"]
    ]);

    $client->customers->update("cus_123", ["name" => "New Name"]);

    $call = $client->getCall("customers.update", 0);

    expect($call)->toBe(["name" => "New Name"]);
});