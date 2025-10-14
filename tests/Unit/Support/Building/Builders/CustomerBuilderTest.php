<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\CustomerBuilder;

describe("CustomerBuilder", function (): void {
    test("can build a basic customer", function (): void {
        $builder = new CustomerBuilder();
        $customer = $builder->build(
            email: "test@example.com"
        );

        expect($customer)
            ->toBeInstanceOf(StripeCustomer::class)
            ->and($customer->email)->toBe("test@example.com");
    });

    test("can build a customer with all parameters", function (): void {
        $builder = new CustomerBuilder();
        $customer = $builder->build(
            id: "cus_123",
            email: "test@example.com",
            name: "John Doe",
            description: "Test customer",
            phone: "+1234567890"
        );

        expect($customer)
            ->toBeInstanceOf(StripeCustomer::class)
            ->and($customer->id)->toBe("cus_123")
            ->and($customer->email)->toBe("test@example.com")
            ->and($customer->name)->toBe("John Doe")
            ->and($customer->description)->toBe("Test customer")
            ->and($customer->phone)->toBe("+1234567890");
    });
});