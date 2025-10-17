<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Objects\Webhook\StripeInvoiceLineItem;
use EncoreDigitalGroup\Stripe\Objects\Webhook\StripeInvoiceWebhookData;

test("can create StripeInvoiceWebhookData using make method", function (): void {
    $invoice = StripeInvoiceWebhookData::make(
        id: "in_123",
        number: "INV-001",
        subscription: "sub_123",
        total: 2000,
        status: "paid"
    );

    expect($invoice)
        ->toBeInstanceOf(StripeInvoiceWebhookData::class)
        ->and($invoice->id)->toBe("in_123")
        ->and($invoice->number)->toBe("INV-001")
        ->and($invoice->subscription)->toBe("sub_123")
        ->and($invoice->total)->toBe(2000)
        ->and($invoice->status)->toBe("paid");
});

test("can create StripeInvoiceWebhookData from webhook data", function (): void {
    $webhookData = [
        "id" => "in_123",
        "number" => "INV-001",
        "subscription" => "sub_123",
        "payment_intent" => "pi_123",
        "customer" => "cus_123",
        "subtotal" => 1800,
        "tax" => 200,
        "total" => 2000,
        "amount_due" => 2000,
        "amount_paid" => 2000,
        "amount_remaining" => 0,
        "status" => "paid",
        "currency" => "usd",
        "created" => 1234567890,
        "due_date" => 1234567900,
        "lines" => [
            "data" => [
                [
                    "id" => "il_123",
                    "description" => "Line Item 1",
                    "amount" => 1000,
                    "quantity" => 1,
                    "unit_amount" => 1000,
                ],
                [
                    "id" => "il_456",
                    "description" => "Line Item 2",
                    "amount" => 800,
                    "quantity" => 1,
                    "unit_amount" => 800,
                ],
            ],
        ],
        "metadata" => [
            "order_id" => "12345",
        ],
    ];

    $invoice = StripeInvoiceWebhookData::fromWebhookData($webhookData);

    expect($invoice)
        ->toBeInstanceOf(StripeInvoiceWebhookData::class)
        ->and($invoice->id)->toBe("in_123")
        ->and($invoice->number)->toBe("INV-001")
        ->and($invoice->subscription)->toBe("sub_123")
        ->and($invoice->paymentIntent)->toBe("pi_123")
        ->and($invoice->customer)->toBe("cus_123")
        ->and($invoice->subtotal)->toBe(1800)
        ->and($invoice->tax)->toBe(200)
        ->and($invoice->total)->toBe(2000)
        ->and($invoice->status)->toBe("paid")
        ->and($invoice->currency)->toBe("usd")
        ->and($invoice->created)->toBeInstanceOf(CarbonImmutable::class)
        ->and($invoice->dueDate)->toBeInstanceOf(CarbonImmutable::class)
        ->and($invoice->lines)->toBeArray()
        ->and($invoice->lines)->toHaveCount(2)
        ->and($invoice->lines[0])->toBeInstanceOf(StripeInvoiceLineItem::class)
        ->and($invoice->lines[0]->id)->toBe("il_123")
        ->and($invoice->lines[1]->id)->toBe("il_456")
        ->and($invoice->metadata)->toBeArray();
});

test("fromWebhookData handles missing lines", function (): void {
    $webhookData = [
        "id" => "in_123",
        "total" => 2000,
    ];

    $invoice = StripeInvoiceWebhookData::fromWebhookData($webhookData);

    expect($invoice->lines)->toBeArray()
        ->and($invoice->lines)->toBeEmpty();
});

test("fromWebhookData handles missing timestamps", function (): void {
    $webhookData = [
        "id" => "in_123",
    ];

    $invoice = StripeInvoiceWebhookData::fromWebhookData($webhookData);

    expect($invoice->created)->toBeNull()
        ->and($invoice->dueDate)->toBeNull();
});

test("toArray returns correct structure", function (): void {
    $lineItem = StripeInvoiceLineItem::make(
        id: "il_123",
        description: "Test Item",
        amount: 1000
    );

    $invoice = StripeInvoiceWebhookData::make(
        id: "in_123",
        number: "INV-001",
        total: 1000,
        lines: [$lineItem]
    );

    $array = $invoice->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("id")
        ->and($array)->toHaveKey("number")
        ->and($array)->toHaveKey("total")
        ->and($array)->toHaveKey("lines")
        ->and($array["lines"])->toBeArray()
        ->and($array["lines"])->toHaveCount(1);
});

test("toArray filters null values", function (): void {
    $invoice = StripeInvoiceWebhookData::make(
        id: "in_123",
        total: 2000
    );

    $array = $invoice->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("id")
        ->and($array)->toHaveKey("total")
        ->and($array)->not->toHaveKey("number")
        ->and($array)->not->toHaveKey("subscription");
});

test("toArray handles timestamps correctly", function (): void {
    $created = CarbonImmutable::createFromTimestamp(1234567890);
    $invoice = StripeInvoiceWebhookData::make(
        id: "in_123",
        total: 2000,
        created: $created
    );

    $array = $invoice->toArray();

    expect($array)->toHaveKey("created")
        ->and($array["created"])->toBe(1234567890);
});
