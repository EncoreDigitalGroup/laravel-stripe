<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Webhook\StripeInvoiceLineItem;

test("can create StripeInvoiceLineItem using make method", function (): void {
    $lineItem = StripeInvoiceLineItem::make(
        id: "il_123",
        description: "Test Line Item",
        amount: 1000,
        quantity: 2,
        unitAmount: 500
    );

    expect($lineItem)
        ->toBeInstanceOf(StripeInvoiceLineItem::class)
        ->and($lineItem->id)->toBe("il_123")
        ->and($lineItem->description)->toBe("Test Line Item")
        ->and($lineItem->amount)->toBe(1000)
        ->and($lineItem->quantity)->toBe(2)
        ->and($lineItem->unitAmount)->toBe(500);
});

test("can create StripeInvoiceLineItem from webhook data", function (): void {
    $webhookData = [
        "id" => "il_123",
        "description" => "Subscription Item",
        "amount" => 2000,
        "quantity" => 1,
        "unit_amount" => 2000,
        "price" => [
            "id" => "price_123",
            "product" => "prod_123",
            "recurring" => [
                "interval" => "month",
            ],
        ],
        "metadata" => [
            "key" => "value",
        ],
    ];

    $lineItem = StripeInvoiceLineItem::fromWebhookData($webhookData);

    expect($lineItem)
        ->toBeInstanceOf(StripeInvoiceLineItem::class)
        ->and($lineItem->id)->toBe("il_123")
        ->and($lineItem->description)->toBe("Subscription Item")
        ->and($lineItem->amount)->toBe(2000)
        ->and($lineItem->quantity)->toBe(1)
        ->and($lineItem->unitAmount)->toBe(2000)
        ->and($lineItem->priceId)->toBe("price_123")
        ->and($lineItem->productId)->toBe("prod_123")
        ->and($lineItem->price)->toBeArray()
        ->and($lineItem->metadata)->toBeArray();
});

test("fromWebhookData handles missing fields", function (): void {
    $webhookData = [
        "id" => "il_123",
    ];

    $lineItem = StripeInvoiceLineItem::fromWebhookData($webhookData);

    expect($lineItem->id)->toBe("il_123")
        ->and($lineItem->description)->toBeNull()
        ->and($lineItem->amount)->toBeNull()
        ->and($lineItem->quantity)->toBeNull();
});

test("toArray returns correct structure", function (): void {
    $lineItem = StripeInvoiceLineItem::make(
        id: "il_123",
        description: "Test Item",
        amount: 1000,
        quantity: 1,
        unitAmount: 1000,
        priceId: "price_123",
        productId: "prod_123"
    );

    $array = $lineItem->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("id")
        ->and($array)->toHaveKey("description")
        ->and($array)->toHaveKey("amount")
        ->and($array)->toHaveKey("quantity")
        ->and($array)->toHaveKey("unit_amount")
        ->and($array)->toHaveKey("price_id")
        ->and($array)->toHaveKey("product_id");
});

test("toArray filters null values", function (): void {
    $lineItem = StripeInvoiceLineItem::make(
        id: "il_123",
        amount: 1000
    );

    $array = $lineItem->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("id")
        ->and($array)->toHaveKey("amount")
        ->and($array)->not->toHaveKey("description")
        ->and($array)->not->toHaveKey("quantity");
});
