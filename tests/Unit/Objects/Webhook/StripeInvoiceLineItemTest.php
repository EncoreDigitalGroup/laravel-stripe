<?php



use EncoreDigitalGroup\Stripe\Objects\Webhook\Payloads\StripeInvoiceLineItemWebhookData;
use Stripe\StripeObject;

test("can create StripeInvoiceLineItem using make method", function (): void {
    $lineItem = StripeInvoiceLineItemWebhookData::make()
        ->withId("il_123")
        ->withDescription("Test Line Item")
        ->withAmount(1000)
        ->withQuantity(2)
        ->withUnitAmount(500);

    expect($lineItem)
        ->toBeInstanceOf(StripeInvoiceLineItemWebhookData::class)
        ->and($lineItem->id())->toBe("il_123")
        ->and($lineItem->description())->toBe("Test Line Item")
        ->and($lineItem->amount())->toBe(1000)
        ->and($lineItem->quantity())->toBe(2)
        ->and($lineItem->unitAmount())->toBe(500);
});

test("can create StripeInvoiceLineItem from Stripe object", function (): void {
    $stripeLineItem = StripeObject::constructFrom([
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
    ]);

    $lineItem = StripeInvoiceLineItemWebhookData::fromStripeObject($stripeLineItem);

    expect($lineItem)
        ->toBeInstanceOf(StripeInvoiceLineItemWebhookData::class)
        ->and($lineItem->id())->toBe("il_123")
        ->and($lineItem->description())->toBe("Subscription Item")
        ->and($lineItem->amount())->toBe(2000)
        ->and($lineItem->quantity())->toBe(1)
        ->and($lineItem->unitAmount())->toBe(2000)
        ->and($lineItem->priceId())->toBe("price_123")
        ->and($lineItem->productId())->toBe("prod_123")
        ->and($lineItem->price())->toBeArray()
        ->and($lineItem->metadata())->toBeArray();
});

test("fromStripeObject handles missing fields", function (): void {
    $stripeLineItem = StripeObject::constructFrom([
        "id" => "il_123",
    ]);

    $lineItem = StripeInvoiceLineItemWebhookData::fromStripeObject($stripeLineItem);

    expect($lineItem->id())->toBe("il_123")
        ->and($lineItem->description())->toBeNull()
        ->and($lineItem->amount())->toBeNull()
        ->and($lineItem->quantity())->toBeNull();
});

test("toArray returns correct structure", function (): void {
    $lineItem = StripeInvoiceLineItemWebhookData::make()
        ->withId("il_123")
        ->withDescription("Test Item")
        ->withAmount(1000)
        ->withQuantity(1)
        ->withUnitAmount(1000)
        ->withPriceId("price_123")
        ->withProductId("prod_123");

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
    $lineItem = StripeInvoiceLineItemWebhookData::make()
        ->withId("il_123")
        ->withAmount(1000);

    $array = $lineItem->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("id")
        ->and($array)->toHaveKey("amount")
        ->and($array)->not->toHaveKey("description")
        ->and($array)->not->toHaveKey("quantity");
});
