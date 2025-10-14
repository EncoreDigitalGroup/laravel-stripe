<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeFinancialConnection;

test("can create financial connection with customer", function (): void {
    $customer = StripeCustomer::make(
        id: "cus_test",
        email: "test@example.com"
    );

    $connection = StripeFinancialConnection::make(
        customer: $customer
    );

    expect($connection->customer)->toBe($customer)
        ->and($connection->permissions)->toBe(["transactions"]);
});

test("can create financial connection with custom permissions", function (): void {
    $customer = StripeCustomer::make(id: "cus_test");

    $connection = StripeFinancialConnection::make(
        customer: $customer,
        permissions: ["transactions", "balances", "ownership"]
    );

    expect($connection->permissions)->toBe(["transactions", "balances", "ownership"]);
});

test("toArray returns correct structure", function (): void {
    $customer = StripeCustomer::make(
        id: "cus_test123",
        email: "test@example.com"
    );

    $connection = StripeFinancialConnection::make(
        customer: $customer,
        permissions: ["transactions", "payment_method"]
    );

    $array = $connection->toArray();

    expect($array)->toBe([
        "account_holder" => [
            "type" => "customer",
            "customer" => "cus_test123",
        ],
        "permissions" => ["transactions", "payment_method"],
    ]);
});