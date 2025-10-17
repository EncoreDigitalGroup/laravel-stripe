<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Objects\Webhook\StripeInvoiceWebhookData;
use EncoreDigitalGroup\Stripe\Objects\Webhook\StripePaymentIntentWebhookData;
use EncoreDigitalGroup\Stripe\Objects\Webhook\StripeWebhookEvent;

test("can create StripeWebhookEvent using make method", function (): void {
    $event = StripeWebhookEvent::make(
        id: "evt_123",
        type: "invoice.created"
    );

    expect($event)
        ->toBeInstanceOf(StripeWebhookEvent::class)
        ->and($event->id)->toBe("evt_123")
        ->and($event->type)->toBe("invoice.created");
});

test("can create StripeWebhookEvent from invoice webhook data", function (): void {
    $webhookData = [
        "id" => "evt_123",
        "type" => "invoice.created",
        "created" => 1234567890,
        "livemode" => true,
        "api_version" => "2023-10-16",
        "data" => [
            "object" => [
                "id" => "in_123",
                "number" => "INV-001",
                "total" => 2000,
                "status" => "draft",
                "subscription" => "sub_123",
            ],
        ],
    ];

    $event = StripeWebhookEvent::fromWebhookData($webhookData);

    expect($event)
        ->toBeInstanceOf(StripeWebhookEvent::class)
        ->and($event->id)->toBe("evt_123")
        ->and($event->type)->toBe("invoice.created")
        ->and($event->created)->toBeInstanceOf(CarbonImmutable::class)
        ->and($event->livemode)->toBe(true)
        ->and($event->apiVersion)->toBe("2023-10-16")
        ->and($event->data)->toBeInstanceOf(StripeInvoiceWebhookData::class);
});

test("can create StripeWebhookEvent from payment intent webhook data", function (): void {
    $webhookData = [
        "id" => "evt_456",
        "type" => "payment_intent.succeeded",
        "created" => 1234567890,
        "data" => [
            "object" => [
                "id" => "pi_123",
                "status" => "succeeded",
                "amount" => 2000,
                "currency" => "usd",
            ],
        ],
    ];

    $event = StripeWebhookEvent::fromWebhookData($webhookData);

    expect($event)
        ->toBeInstanceOf(StripeWebhookEvent::class)
        ->and($event->id)->toBe("evt_456")
        ->and($event->type)->toBe("payment_intent.succeeded")
        ->and($event->data)->toBeInstanceOf(StripePaymentIntentWebhookData::class);
});

test("fromWebhookData handles unknown event types as raw array", function (): void {
    $webhookData = [
        "id" => "evt_789",
        "type" => "customer.created",
        "data" => [
            "object" => [
                "id" => "cus_123",
                "email" => "test@example.com",
            ],
        ],
    ];

    $event = StripeWebhookEvent::fromWebhookData($webhookData);

    expect($event->type)->toBe("customer.created")
        ->and($event->data)->toBeArray()
        ->and($event->data["id"])->toBe("cus_123");
});

test("asInvoiceData returns invoice data when event is invoice-related", function (): void {
    $webhookData = [
        "id" => "evt_123",
        "type" => "invoice.finalized",
        "data" => [
            "object" => [
                "id" => "in_123",
                "total" => 2000,
            ],
        ],
    ];

    $event = StripeWebhookEvent::fromWebhookData($webhookData);
    $invoiceData = $event->asInvoiceData();

    expect($invoiceData)->toBeInstanceOf(StripeInvoiceWebhookData::class)
        ->and($invoiceData->id)->toBe("in_123");
});

test("asInvoiceData returns null when event is not invoice-related", function (): void {
    $webhookData = [
        "id" => "evt_456",
        "type" => "payment_intent.succeeded",
        "data" => [
            "object" => [
                "id" => "pi_123",
                "status" => "succeeded",
            ],
        ],
    ];

    $event = StripeWebhookEvent::fromWebhookData($webhookData);

    expect($event->asInvoiceData())->toBeNull();
});

test("asPaymentIntentData returns payment intent data when event is payment intent-related", function (): void {
    $webhookData = [
        "id" => "evt_456",
        "type" => "payment_intent.failed",
        "data" => [
            "object" => [
                "id" => "pi_123",
                "status" => "failed",
            ],
        ],
    ];

    $event = StripeWebhookEvent::fromWebhookData($webhookData);
    $paymentIntentData = $event->asPaymentIntentData();

    expect($paymentIntentData)->toBeInstanceOf(StripePaymentIntentWebhookData::class)
        ->and($paymentIntentData->id)->toBe("pi_123");
});

test("asPaymentIntentData returns null when event is not payment intent-related", function (): void {
    $webhookData = [
        "id" => "evt_123",
        "type" => "invoice.created",
        "data" => [
            "object" => [
                "id" => "in_123",
            ],
        ],
    ];

    $event = StripeWebhookEvent::fromWebhookData($webhookData);

    expect($event->asPaymentIntentData())->toBeNull();
});

test("asRawData returns raw array for unknown event types", function (): void {
    $webhookData = [
        "id" => "evt_789",
        "type" => "customer.created",
        "data" => [
            "object" => [
                "id" => "cus_123",
                "email" => "test@example.com",
            ],
        ],
    ];

    $event = StripeWebhookEvent::fromWebhookData($webhookData);
    $rawData = $event->asRawData();

    expect($rawData)->toBeArray()
        ->and($rawData["id"])->toBe("cus_123")
        ->and($rawData["email"])->toBe("test@example.com");
});

test("asRawData returns null for typed events", function (): void {
    $webhookData = [
        "id" => "evt_123",
        "type" => "invoice.created",
        "data" => [
            "object" => [
                "id" => "in_123",
            ],
        ],
    ];

    $event = StripeWebhookEvent::fromWebhookData($webhookData);

    expect($event->asRawData())->toBeNull();
});

test("toArray returns correct structure for invoice events", function (): void {
    $invoiceData = StripeInvoiceWebhookData::make(
        id: "in_123",
        total: 2000
    );

    $event = StripeWebhookEvent::make(
        id: "evt_123",
        type: "invoice.created",
        data: $invoiceData
    );

    $array = $event->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("id")
        ->and($array)->toHaveKey("type")
        ->and($array)->toHaveKey("data")
        ->and($array["data"])->toBeArray()
        ->and($array["data"])->toHaveKey("object");
});

test("toArray filters null values", function (): void {
    $event = StripeWebhookEvent::make(
        id: "evt_123",
        type: "invoice.created"
    );

    $array = $event->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("id")
        ->and($array)->toHaveKey("type")
        ->and($array)->not->toHaveKey("livemode")
        ->and($array)->not->toHaveKey("api_version");
});
