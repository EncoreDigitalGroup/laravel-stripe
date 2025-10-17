<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Objects\Webhook\StripePaymentIntentWebhookData;

test("can create StripePaymentIntentWebhookData using make method", function (): void {
    $paymentIntent = StripePaymentIntentWebhookData::make(
        id: "pi_123",
        status: "succeeded",
        amount: 2000,
        currency: "usd"
    );

    expect($paymentIntent)
        ->toBeInstanceOf(StripePaymentIntentWebhookData::class)
        ->and($paymentIntent->id)->toBe("pi_123")
        ->and($paymentIntent->status)->toBe("succeeded")
        ->and($paymentIntent->amount)->toBe(2000)
        ->and($paymentIntent->currency)->toBe("usd");
});

test("can create StripePaymentIntentWebhookData from webhook data", function (): void {
    $webhookData = [
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
    ];

    $paymentIntent = StripePaymentIntentWebhookData::fromWebhookData($webhookData);

    expect($paymentIntent)
        ->toBeInstanceOf(StripePaymentIntentWebhookData::class)
        ->and($paymentIntent->id)->toBe("pi_123")
        ->and($paymentIntent->status)->toBe("succeeded")
        ->and($paymentIntent->amount)->toBe(2000)
        ->and($paymentIntent->amountReceived)->toBe(2000)
        ->and($paymentIntent->currency)->toBe("usd")
        ->and($paymentIntent->customer)->toBe("cus_123")
        ->and($paymentIntent->invoice)->toBe("in_123")
        ->and($paymentIntent->paymentMethod)->toBe("pm_123")
        ->and($paymentIntent->description)->toBe("Payment for Invoice INV-001")
        ->and($paymentIntent->created)->toBeInstanceOf(CarbonImmutable::class)
        ->and($paymentIntent->metadata)->toBeArray();
});

test("fromWebhookData handles payment failure data", function (): void {
    $webhookData = [
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
    ];

    $paymentIntent = StripePaymentIntentWebhookData::fromWebhookData($webhookData);

    expect($paymentIntent->status)->toBe("failed")
        ->and($paymentIntent->cancellationReason)->toBe("abandoned")
        ->and($paymentIntent->lastPaymentError)->toBeArray()
        ->and($paymentIntent->lastPaymentError["code"])->toBe("card_declined");
});

test("fromWebhookData handles missing fields", function (): void {
    $webhookData = [
        "id" => "pi_123",
        "status" => "succeeded",
    ];

    $paymentIntent = StripePaymentIntentWebhookData::fromWebhookData($webhookData);

    expect($paymentIntent->id)->toBe("pi_123")
        ->and($paymentIntent->status)->toBe("succeeded")
        ->and($paymentIntent->amount)->toBeNull()
        ->and($paymentIntent->customer)->toBeNull();
});

test("toArray returns correct structure", function (): void {
    $paymentIntent = StripePaymentIntentWebhookData::make(
        id: "pi_123",
        status: "succeeded",
        amount: 2000,
        currency: "usd",
        customer: "cus_123"
    );

    $array = $paymentIntent->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("id")
        ->and($array)->toHaveKey("status")
        ->and($array)->toHaveKey("amount")
        ->and($array)->toHaveKey("currency")
        ->and($array)->toHaveKey("customer");
});

test("toArray filters null values", function (): void {
    $paymentIntent = StripePaymentIntentWebhookData::make(
        id: "pi_123",
        status: "succeeded"
    );

    $array = $paymentIntent->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("id")
        ->and($array)->toHaveKey("status")
        ->and($array)->not->toHaveKey("amount")
        ->and($array)->not->toHaveKey("customer");
});

test("toArray handles timestamps correctly", function (): void {
    $created = CarbonImmutable::createFromTimestamp(1234567890);
    $paymentIntent = StripePaymentIntentWebhookData::make(
        id: "pi_123",
        status: "succeeded",
        created: $created
    );

    $array = $paymentIntent->toArray();

    expect($array)->toHaveKey("created")
        ->and($array["created"])->toBe(1234567890);
});
