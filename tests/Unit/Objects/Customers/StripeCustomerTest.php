<?php

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeShipping;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use Stripe\Util\Util;

test("can create StripeCustomer using make method", function (): void {
    $customer = StripeCustomer::make()
        ->withEmail("test@example.com")
        ->withName("Test User")
        ->withPhone("+1234567890");

    expect($customer)
        ->toBeInstanceOf(StripeCustomer::class)
        ->and($customer->email())->toBe("test@example.com")
        ->and($customer->name())->toBe("Test User")
        ->and($customer->phone())->toBe("+1234567890");
});

test("can create StripeCustomer from Stripe object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "cus_123",
        "object" => "customer",
        "email" => "test@example.com",
        "name" => "Test User",
        "phone" => "+1234567890",
        "description" => "Test Description",
        "address" => [
            "line1" => "123 Main St",
            "line2" => "Apt 4",
            "city" => "San Francisco",
            "state" => "CA",
            "postal_code" => "94102",
            "country" => "US",
        ],
        "shipping" => [
            "name" => "Shipping Name",
            "phone" => "+0987654321",
            "address" => [
                "line1" => "456 Ship St",
                "city" => "Los Angeles",
                "state" => "CA",
                "postal_code" => "90001",
                "country" => "US",
            ],
        ],
    ], []);

    $customer = StripeCustomer::fromStripeObject($stripeObject);

    expect($customer)
        ->toBeInstanceOf(StripeCustomer::class)
        ->and($customer->id())->toBe("cus_123")
        ->and($customer->email())->toBe("test@example.com")
        ->and($customer->name())->toBe("Test User")
        ->and($customer->phone())->toBe("+1234567890")
        ->and($customer->description())->toBe("Test Description")
        ->and($customer->address())->toBeInstanceOf(StripeAddress::class)
        ->and($customer->address()->line1())->toBe("123 Main St")
        ->and($customer->address()->city())->toBe("San Francisco")
        ->and($customer->shipping())->toBeInstanceOf(StripeShipping::class)
        ->and($customer->shipping()->name())->toBe("Shipping Name")
        ->and($customer->shipping()->address())->toBeInstanceOf(StripeAddress::class)
        ->and($customer->shipping()->address()->line1())->toBe("456 Ship St");
});

test("fromStripeObject handles missing address", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "cus_123",
        "object" => "customer",
        "email" => "test@example.com",
        "address" => null,
    ], []);

    $customer = StripeCustomer::fromStripeObject($stripeObject);

    expect($customer->address())->toBeNull();
});

test("fromStripeObject handles missing shipping", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "cus_123",
        "object" => "customer",
        "email" => "test@example.com",
        "shipping" => null,
    ], []);

    $customer = StripeCustomer::fromStripeObject($stripeObject);

    expect($customer->shipping())->toBeNull();
});

test("toArray returns correct structure with address", function (): void {
    $address = StripeAddress::make()
        ->withLine1("123 Main St")
        ->withCity("San Francisco")
        ->withState("CA")
        ->withPostalCode("94102")
        ->withCountry("US");

    $customer = StripeCustomer::make()
        ->withId("cus_123")
        ->withEmail("test@example.com")
        ->withName("Test User")
        ->withAddress($address);

    $array = $customer->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("id")
        ->and($array)->toHaveKey("email")
        ->and($array)->toHaveKey("name")
        ->and($array)->toHaveKey("address")
        ->and($array["address"])->toBeArray()
        ->and($array["address"])->toHaveKey("line1");
});

test("toArray returns correct structure with shipping", function (): void {
    $shippingAddress = StripeAddress::make()
        ->withLine1("456 Ship St")
        ->withCity("Los Angeles")
        ->withState("CA")
        ->withPostalCode("90001")
        ->withCountry("US");

    $shipping = StripeShipping::make()
        ->withName("Shipping Name")
        ->withPhone("+0987654321")
        ->withAddress($shippingAddress);

    $customer = StripeCustomer::make()
        ->withEmail("test@example.com")
        ->withShipping($shipping);

    $array = $customer->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("shipping")
        ->and($array["shipping"])->toBeArray()
        ->and($array["shipping"])->toHaveKey("name")
        ->and($array["shipping"])->toHaveKey("address");
});

test("toArray filters null values", function (): void {
    $customer = StripeCustomer::make()
        ->withEmail("test@example.com");

    $array = $customer->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("email")
        ->and($array)->not->toHaveKey("id")
        ->and($array)->not->toHaveKey("name")
        ->and($array)->not->toHaveKey("address");
});
