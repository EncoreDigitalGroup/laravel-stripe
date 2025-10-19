<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Enums\PaymentMethodType;
use EncoreDigitalGroup\Stripe\Objects\Payment\StripePaymentMethod;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use Stripe\Util\Util;

test("can create StripePaymentMethod using make method", function (): void {
    $paymentMethod = StripePaymentMethod::make()
        ->withType(PaymentMethodType::Card)
        ->withCustomer("cus_123");

    expect($paymentMethod)
        ->toBeInstanceOf(StripePaymentMethod::class)
        ->and($paymentMethod->type())->toBe(PaymentMethodType::Card)
        ->and($paymentMethod->customer())->toBe("cus_123");
});

test("can create StripePaymentMethod from Stripe object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "pm_123",
        "object" => "payment_method",
        "type" => "card",
        "customer" => "cus_456",
        "created" => 1234567890,
        "billing_details" => [
            "address" => [
                "line1" => "123 Main St",
                "line2" => "Apt 4",
                "city" => "San Francisco",
                "state" => "CA",
                "postal_code" => "94102",
                "country" => "US",
            ],
        ],
        "card" => [
            "brand" => "visa",
            "last4" => "4242",
            "exp_month" => 12,
            "exp_year" => 2025,
        ],
        "metadata" => ["key" => "value"],
    ], null);

    $paymentMethod = StripePaymentMethod::fromStripeObject($stripeObject);

    expect($paymentMethod)
        ->toBeInstanceOf(StripePaymentMethod::class)
        ->and($paymentMethod->id())->toBe("pm_123")
        ->and($paymentMethod->type())->toBe(PaymentMethodType::Card)
        ->and($paymentMethod->customer())->toBe("cus_456")
        ->and($paymentMethod->created())->toBeInstanceOf(CarbonImmutable::class)
        ->and($paymentMethod->billingDetails())->toBeInstanceOf(StripeAddress::class)
        ->and($paymentMethod->billingDetails()->line1())->toBe("123 Main St")
        ->and($paymentMethod->billingDetails()->city())->toBe("San Francisco")
        ->and($paymentMethod->card())->toBeInstanceOf(\Illuminate\Support\Collection::class)
        ->and($paymentMethod->card()->get("brand"))->toBe("visa")
        ->and($paymentMethod->card()->get("last4"))->toBe("4242")
        ->and($paymentMethod->metadata())->toBe(["key" => "value"]);
});

test("can convert StripePaymentMethod to array", function (): void {
    $address = StripeAddress::make()
        ->withLine1("456 Oak Ave")
        ->withCity("New York")
        ->withState("NY")
        ->withPostalCode("10001")
        ->withCountry("US");

    $paymentMethod = StripePaymentMethod::make()
        ->withType(PaymentMethodType::Card)
        ->withCustomer("cus_789")
        ->withBillingDetails($address);

    $array = $paymentMethod->toArray();

    expect($array)
        ->toBeArray()
        ->and($array["type"])->toBe("card")
        ->and($array["customer"])->toBe("cus_789")
        ->and($array["billing_details"])->toBeArray()
        ->and($array["billing_details"]["address"])->toBeArray()
        ->and($array["billing_details"]["address"]["line1"])->toBe("456 Oak Ave")
        ->and($array["billing_details"]["address"]["city"])->toBe("New York");
});

test("toArray filters null values", function (): void {
    $paymentMethod = StripePaymentMethod::make()
        ->withType(PaymentMethodType::Card);

    $array = $paymentMethod->toArray();

    expect($array)->toHaveKey("type")
        ->and($array)->not->toHaveKey("customer")
        ->and($array)->not->toHaveKey("billing_details");
});

test("can handle different payment method types", function (): void {
    $paymentMethod = StripePaymentMethod::make()
        ->withType(PaymentMethodType::UsBankAccount);

    expect($paymentMethod->type())->toBe(PaymentMethodType::UsBankAccount);

    $array = $paymentMethod->toArray();
    expect($array["type"])->toBe("us_bank_account");
});

test("can handle us bank account data", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "pm_bank",
        "object" => "payment_method",
        "type" => "us_bank_account",
        "us_bank_account" => [
            "account_holder_type" => "individual",
            "account_type" => "checking",
            "bank_name" => "STRIPE TEST BANK",
            "last4" => "6789",
            "routing_number" => "110000000",
        ],
    ], null);

    $paymentMethod = StripePaymentMethod::fromStripeObject($stripeObject);

    expect($paymentMethod->usBankAccount())->toBeInstanceOf(\Illuminate\Support\Collection::class)
        ->and($paymentMethod->usBankAccount()->get("bank_name"))->toBe("STRIPE TEST BANK")
        ->and($paymentMethod->usBankAccount()->get("last4"))->toBe("6789");
});

test("can handle nested customer object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "pm_123",
        "object" => "payment_method",
        "type" => "card",
        "customer" => [
            "id" => "cus_nested",
            "object" => "customer",
            "email" => "test@example.com",
        ],
    ], null);

    $paymentMethod = StripePaymentMethod::fromStripeObject($stripeObject);

    expect($paymentMethod->customer())->toBe("cus_nested");
});

test("billing details can be null", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "pm_123",
        "object" => "payment_method",
        "type" => "card",
        "billing_details" => [
            "address" => null,
        ],
    ], null);

    $paymentMethod = StripePaymentMethod::fromStripeObject($stripeObject);

    expect($paymentMethod->billingDetails())->toBeNull();
});
