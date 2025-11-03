<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */
use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Enums\PaymentIntentCaptureMethod;
use EncoreDigitalGroup\Stripe\Enums\PaymentIntentConfirmationMethod;
use EncoreDigitalGroup\Stripe\Enums\PaymentIntentStatus;
use EncoreDigitalGroup\Stripe\Enums\PaymentMethodType;
use EncoreDigitalGroup\Stripe\Objects\Payment\StripePaymentIntent;
use Illuminate\Support\Collection;
use Stripe\Util\Util;

test("can create StripePaymentIntent using make method", function (): void {
    $paymentIntent = StripePaymentIntent::make()
        ->withAmount(1000)
        ->withCurrency("usd")
        ->withCustomer("cus_123")
        ->withDescription("Test payment");

    expect($paymentIntent)
        ->toBeInstanceOf(StripePaymentIntent::class)
        ->and($paymentIntent->amount())->toBe(1000)
        ->and($paymentIntent->currency())->toBe("usd")
        ->and($paymentIntent->customer())->toBe("cus_123")
        ->and($paymentIntent->description())->toBe("Test payment");
});

test("can create StripePaymentIntent from Stripe object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "pi_123",
        "object" => "payment_intent",
        "amount" => 2000,
        "amount_capturable" => 0,
        "amount_received" => 2000,
        "currency" => "usd",
        "customer" => "cus_456",
        "description" => "Payment for services",
        "invoice" => "in_789",
        "payment_method" => "pm_abc",
        "status" => "succeeded",
        "capture_method" => "automatic",
        "confirmation_method" => "automatic",
        "created" => 1234567890,
        "client_secret" => "pi_123_secret_xyz",
        "payment_method_types" => ["card"],
        "metadata" => ["order_id" => "order_123"],
    ], null);

    $paymentIntent = StripePaymentIntent::fromStripeObject($stripeObject);

    expect($paymentIntent)
        ->toBeInstanceOf(StripePaymentIntent::class)
        ->and($paymentIntent->id())->toBe("pi_123")
        ->and($paymentIntent->amount())->toBe(2000)
        ->and($paymentIntent->amountCapturable())->toBe(0)
        ->and($paymentIntent->amountReceived())->toBe(2000)
        ->and($paymentIntent->currency())->toBe("usd")
        ->and($paymentIntent->customer())->toBe("cus_456")
        ->and($paymentIntent->description())->toBe("Payment for services")
        ->and($paymentIntent->invoice())->toBe("in_789")
        ->and($paymentIntent->paymentMethod())->toBe("pm_abc")
        ->and($paymentIntent->status())->toBe(PaymentIntentStatus::Succeeded)
        ->and($paymentIntent->captureMethod())->toBe(PaymentIntentCaptureMethod::Automatic)
        ->and($paymentIntent->confirmationMethod())->toBe(PaymentIntentConfirmationMethod::Automatic)
        ->and($paymentIntent->created())->toBeInstanceOf(CarbonImmutable::class)
        ->and($paymentIntent->clientSecret())->toBe("pi_123_secret_xyz")
        ->and($paymentIntent->paymentMethodTypes())->toBeInstanceOf(Collection::class)
        ->and($paymentIntent->paymentMethodTypes()->first())->toBe(PaymentMethodType::Card)
        ->and($paymentIntent->metadata())->toBe(["order_id" => "order_123"]);
});

test("can convert StripePaymentIntent to array", function (): void {
    $paymentIntent = StripePaymentIntent::make()
        ->withAmount(1500)
        ->withCurrency("eur")
        ->withCustomer("cus_789")
        ->withStatus(PaymentIntentStatus::RequiresPaymentMethod)
        ->withCaptureMethod(PaymentIntentCaptureMethod::Manual);

    $array = $paymentIntent->toArray();

    expect($array)
        ->toBeArray()
        ->and($array["amount"])->toBe(1500)
        ->and($array["currency"])->toBe("eur")
        ->and($array["customer"])->toBe("cus_789")
        ->and($array["status"])->toBe("requires_payment_method")
        ->and($array["capture_method"])->toBe("manual");
});

test("toArray filters null values", function (): void {
    $paymentIntent = StripePaymentIntent::make()
        ->withAmount(1000)
        ->withCurrency("usd");

    $array = $paymentIntent->toArray();

    expect($array)->toHaveKey("amount")
        ->and($array)->toHaveKey("currency")
        ->and($array)->not->toHaveKey("customer")
        ->and($array)->not->toHaveKey("description");
});

test("can set payment method types", function (): void {
    $paymentIntent = StripePaymentIntent::make()
        ->withPaymentMethodTypes(collect([PaymentMethodType::Card, PaymentMethodType::UsBankAccount]));

    expect($paymentIntent->paymentMethodTypes())->toHaveCount(2)
        ->and($paymentIntent->paymentMethodTypes()->first())->toBe(PaymentMethodType::Card)
        ->and($paymentIntent->paymentMethodTypes()->last())->toBe(PaymentMethodType::UsBankAccount);
});

test("can handle nested customer object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "pi_123",
        "object" => "payment_intent",
        "amount" => 1000,
        "currency" => "usd",
        "customer" => [
            "id" => "cus_nested",
            "object" => "customer",
            "email" => "test@example.com",
        ],
        "status" => "requires_payment_method",
    ], null);

    $paymentIntent = StripePaymentIntent::fromStripeObject($stripeObject);

    expect($paymentIntent->customer())->toBe("cus_nested");
});

test("can handle last payment error", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "pi_123",
        "object" => "payment_intent",
        "amount" => 1000,
        "currency" => "usd",
        "status" => "requires_payment_method",
        "last_payment_error" => [
            "type" => "card_error",
            "code" => "card_declined",
            "message" => "Your card was declined.",
        ],
    ], null);

    $paymentIntent = StripePaymentIntent::fromStripeObject($stripeObject);

    expect($paymentIntent->lastPaymentError())
        ->toBeArray()
        ->and($paymentIntent->lastPaymentError()["type"])->toBe("card_error")
        ->and($paymentIntent->lastPaymentError()["code"])->toBe("card_declined");
});

test("converts payment method types to values in toArray", function (): void {
    $paymentIntent = StripePaymentIntent::make()
        ->withAmount(1000)
        ->withCurrency("usd")
        ->withPaymentMethodTypes(collect([PaymentMethodType::Card, PaymentMethodType::Klarna]));

    $array = $paymentIntent->toArray();

    expect($array["payment_method_types"])->toBeArray()
        ->and($array["payment_method_types"])->toBe(["card", "klarna"]);
});
