<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Services\StripeCustomerService;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

test("can create a customer using faked stripe client", function (): void {
    // Arrange: Set up fake Stripe responses using enum
    $fake = Stripe::fake([
        StripeMethod::CustomersCreate->value => StripeFixtures::customer([
            "id" => "cus_test123",
            "email" => "john@example.com",
            "name" => "John Doe",
        ]),
    ]);

    // Act: Create a customer through the service
    $customer = StripeCustomer::make()
        ->withEmail("john@example.com")
        ->withName("John Doe");

    $service = StripeCustomerService::make();
    $result = $service->create($customer);

    // Assert: Verify the response
    expect($result)
        ->toBeInstanceOf(StripeCustomer::class)
        ->and($result->id())->toBe("cus_test123")
        ->and($result->email())->toBe("john@example.com")
        ->and($result->name())->toBe("John Doe")
        // Assert: Verify the Stripe API was called (can use enum or string)
        ->and($fake)->toHaveCalledStripeMethod(StripeMethod::CustomersCreate);

});

test("can retrieve a customer using faked stripe client", function (): void {
    // Arrange
    $fake = Stripe::fake([
        "customers.retrieve" => StripeFixtures::customer([
            "id" => "cus_existing",
            "email" => "existing@example.com",
        ]),
    ]);

    // Act
    $service = StripeCustomerService::make();
    $customer = $service->get("cus_existing");

    // Assert
    expect($customer)
        ->toBeInstanceOf(StripeCustomer::class)
        ->and($customer->id())->toBe("cus_existing")
        ->and($customer->email())->toBe("existing@example.com")
        ->and($fake)->toHaveCalledStripeMethod("customers.retrieve");
});

test("can update a customer using faked stripe client", function (): void {
    // Arrange
    $fake = Stripe::fake([
        "customers.update" => StripeFixtures::customer([
            "id" => "cus_123",
            "email" => "updated@example.com",
            "name" => "Updated Name",
        ]),
    ]);

    // Act
    $customer = StripeCustomer::make()
        ->withEmail("updated@example.com")
        ->withName("Updated Name");

    $service = StripeCustomerService::make();
    $result = $service->update("cus_123", $customer);

    // Assert
    expect($result)
        ->toBeInstanceOf(StripeCustomer::class)
        ->and($result->email())->toBe("updated@example.com")
        ->and($result->name())->toBe("Updated Name")
        ->and($fake)->toHaveCalledStripeMethod("customers.update");
});

test("can delete a customer using faked stripe client", function (): void {
    // Arrange
    $fake = Stripe::fake([
        "customers.delete" => StripeFixtures::deleted("cus_123", "customer"),
    ]);

    // Act
    $service = StripeCustomerService::make();
    $result = $service->delete("cus_123");

    // Assert
    expect($result)->toBeTrue()
        ->and($fake)->toHaveCalledStripeMethod("customers.delete");
});

test("can list customers using faked stripe client", function (): void {
    // Arrange
    $fake = Stripe::fake([
        "customers.all" => StripeFixtures::customerList([
            StripeFixtures::customer(["id" => "cus_1", "email" => "user1@example.com"]),
            StripeFixtures::customer(["id" => "cus_2", "email" => "user2@example.com"]),
            StripeFixtures::customer(["id" => "cus_3", "email" => "user3@example.com"]),
        ]),
    ]);

    // Act
    $service = StripeCustomerService::make();
    $customers = $service->list(["limit" => 10]);

    // Assert
    expect($customers)
        ->toHaveCount(3)
        ->and($customers->first())->toBeInstanceOf(StripeCustomer::class)
        ->and($customers->first()->id())->toBe("cus_1")
        ->and($fake)->toHaveCalledStripeMethod("customers.all");
});

test("can use callable responses for dynamic fake responses", function (): void {
    // Arrange: Use a callable to generate dynamic responses
    Stripe::fake([
        "customers.create" => function (array $params): array {
            return StripeFixtures::customer([
                "id" => "cus_dynamic",
                "email" => $params["email"] ?? "default@example.com",
                "name" => $params["name"] ?? "Default Name",
            ]);
        },
    ]);

    // Act
    $customer = StripeCustomer::make()
        ->withEmail("dynamic@example.com")
        ->withName("Dynamic Name");

    $service = StripeCustomerService::make();
    $result = $service->create($customer);

    // Assert: The callable should have used our params
    expect($result->email())->toBe("dynamic@example.com")
        ->and($result->name())->toBe("Dynamic Name");
});

test("can use wildcard patterns for fake responses", function (): void {
    // Arrange: Use wildcard to match any customer method
    Stripe::fake([
        "customers.*" => StripeFixtures::customer([
            "id" => "cus_wildcard",
            "email" => "wildcard@example.com",
        ]),
    ]);

    // Act: Try different methods
    $service = StripeCustomerService::make();
    $retrieved = $service->get("cus_any");
    $created = $service->create(StripeCustomer::make());

    // Assert: Both should work with the wildcard fake
    expect($retrieved->id())->toBe("cus_wildcard")
        ->and($created->id())->toBe("cus_wildcard");
});

test("throws exception when no fake is registered", function (): void {
    // Arrange: Create fake without registering the method we'll call
    Stripe::fake([]);

    // Act & Assert: Should throw exception
    $service = StripeCustomerService::make();

    expect(fn (): StripeCustomer => $service->get("cus_123"))
        ->toThrow(RuntimeException::class, "No fake registered for Stripe method");
});

test("can assert method was not called", function (): void {
    // Arrange
    $fake = Stripe::fake([
        "customers.create" => StripeFixtures::customer(),
        "customers.delete" => StripeFixtures::deleted("cus_123"),
    ]);

    // Act: Only create, don't delete
    $service = StripeCustomerService::make();
    $service->create(StripeCustomer::make());

    // Assert
    expect($fake)
        ->toHaveCalledStripeMethod("customers.create")
        ->toNotHaveCalledStripeMethod("customers.delete");
});

test("can get call count for a method", function (): void {
    // Arrange
    $fake = Stripe::fake([
        "customers.create" => StripeFixtures::customer(),
    ]);

    // Act: Create multiple customers
    $service = StripeCustomerService::make();
    $service->create(StripeCustomer::make());
    $service->create(StripeCustomer::make());
    $service->create(StripeCustomer::make());

    // Assert
    expect($fake)->toHaveCalledStripeMethodTimes("customers.create", 3);
});

test("can search customers using faked stripe client", function (): void {
    // Arrange
    $fake = Stripe::fake([
        "customers.search" => StripeFixtures::customerList([
            StripeFixtures::customer(["id" => "cus_1", "email" => "search1@example.com"]),
            StripeFixtures::customer(["id" => "cus_2", "email" => "search2@example.com"]),
        ]),
    ]);

    // Act
    $service = StripeCustomerService::make();
    $customers = $service->search("email~'@example.com'");

    // Assert
    expect($customers)
        ->toHaveCount(2)
        ->and($customers->first())->toBeInstanceOf(StripeCustomer::class)
        ->and($fake)->toHaveCalledStripeMethod("customers.search");
});