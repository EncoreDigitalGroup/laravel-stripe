<?php

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Objects\Webhook\Payloads\StripePaymentIntentWebhookData;
use Stripe\StripeObject;

test("can create StripePaymentIntentWebhookData using make method", function (): void {
    $paymentIntent = StripePaymentIntentWebhookData::make()
        ->withId("pi_123")
        ->withStatus("succeeded")
        ->withAmount(2000)
        ->withCurrency("usd");

    expect($paymentIntent)
        ->toBeInstanceOf(StripePaymentIntentWebhookData::class)
        ->and($paymentIntent->id())->toBe("pi_123")
        ->and($paymentIntent->status())->toBe("succeeded")
        ->and($paymentIntent->amount())->toBe(2000)
        ->and($paymentIntent->currency())->toBe("usd");
});

test("can create StripePaymentIntentWebhookData from Stripe object", function (): void {
    $stripePaymentIntent = StripeObject::constructFrom([
        "id" => "pi_123",
        "status" => "succeeded",
        "amount" => 2000,
        "amount_received" => 2000,
        "currency" => "usd",
        "customer" => "cus_123",
        "invoice" => "in_123",
        "payment_method" => "pm_123",
        "description" => "Payment for Invoice INV-001",
        "created" => 1234567890,
        "metadata" => [
            "order_id" => "12345",
        ],
    ]);

    $paymentIntent = StripePaymentIntentWebhookData::fromStripeObject($stripePaymentIntent);

    expect($paymentIntent)
        ->toBeInstanceOf(StripePaymentIntentWebhookData::class)
        ->and($paymentIntent->id())->toBe("pi_123")
        ->and($paymentIntent->status())->toBe("succeeded")
        ->and($paymentIntent->amount())->toBe(2000)
        ->and($paymentIntent->amountReceived())->toBe(2000)
        ->and($paymentIntent->currency())->toBe("usd")
        ->and($paymentIntent->customer())->toBe("cus_123")
        ->and($paymentIntent->invoice())->toBe("in_123")
        ->and($paymentIntent->paymentMethod())->toBe("pm_123")
        ->and($paymentIntent->description())->toBe("Payment for Invoice INV-001")
        ->and($paymentIntent->created())->toBeInstanceOf(CarbonImmutable::class)
        ->and($paymentIntent->metadata())->toBeArray();
});

test("fromStripeObject handles payment failure data", function (): void {
    $stripePaymentIntent = StripeObject::constructFrom([
        "id" => "pi_123",
        "status" => "failed",
        "amount" => 2000,
        "currency" => "usd",
        "cancellation_reason" => "abandoned",
        "last_payment_error" => [
            "code" => "card_declined",
            "message" => "Your card was declined",
            "type" => "card_error",
        ],
    ]);

    $paymentIntent = StripePaymentIntentWebhookData::fromStripeObject($stripePaymentIntent);

    expect($paymentIntent->status())->toBe("failed")
        ->and($paymentIntent->cancellationReason())->toBe("abandoned")
        ->and($paymentIntent->lastPaymentError())->toBeArray()
        ->and($paymentIntent->lastPaymentError()["code"])->toBe("card_declined");
});

test("fromStripeObject handles missing fields", function (): void {
    $stripePaymentIntent = StripeObject::constructFrom([
        "id" => "pi_123",
        "status" => "succeeded",
    ]);

    $paymentIntent = StripePaymentIntentWebhookData::fromStripeObject($stripePaymentIntent);

    expect($paymentIntent->id())->toBe("pi_123")
        ->and($paymentIntent->status())->toBe("succeeded")
        ->and($paymentIntent->amount())->toBeNull()
        ->and($paymentIntent->customer())->toBeNull();
});

test("toArray returns correct structure", function (): void {
    $paymentIntent = StripePaymentIntentWebhookData::make()
        ->withId("pi_123")
        ->withStatus("succeeded")
        ->withAmount(2000)
        ->withCurrency("usd")
        ->withCustomer("cus_123");

    $array = $paymentIntent->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("id")
        ->and($array)->toHaveKey("status")
        ->and($array)->toHaveKey("amount")
        ->and($array)->toHaveKey("currency")
        ->and($array)->toHaveKey("customer");
});

test("toArray filters null values", function (): void {
    $paymentIntent = StripePaymentIntentWebhookData::make()
        ->withId("pi_123")
        ->withStatus("succeeded");

    $array = $paymentIntent->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("id")
        ->and($array)->toHaveKey("status")
        ->and($array)->not->toHaveKey("amount")
        ->and($array)->not->toHaveKey("customer");
});

test("toArray handles timestamps correctly", function (): void {
    $created = CarbonImmutable::createFromTimestamp(1234567890);
    $paymentIntent = StripePaymentIntentWebhookData::make()
        ->withId("pi_123")
        ->withStatus("succeeded")
        ->withCreated($created);

    $array = $paymentIntent->toArray();

    expect($array)->toHaveKey("created")
        ->and($array["created"])->toBe(1234567890);
});
