<?php

use EncoreDigitalGroup\Stripe\Objects\Invoice\StripeInvoiceLineItem;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use Stripe\Util\Util;

describe("StripeInvoiceLineItem", function (): void {
    test("can create StripeInvoiceLineItem using make method", function (): void {
        $lineItem = StripeInvoiceLineItem::make()
            ->withId("il_123")
            ->withDescription("Test Line Item")
            ->withAmount(1000)
            ->withCurrency("usd")
            ->withQuantity(1)
            ->withUnitAmount(1000);

        expect($lineItem)
            ->toBeInstanceOf(StripeInvoiceLineItem::class)
            ->and($lineItem->id())->toBe("il_123")
            ->and($lineItem->description())->toBe("Test Line Item")
            ->and($lineItem->amount())->toBe(1000)
            ->and($lineItem->currency())->toBe("usd")
            ->and($lineItem->quantity())->toBe(1)
            ->and($lineItem->unitAmount())->toBe(1000);
    });

    test("can create StripeInvoiceLineItem from Stripe object", function (): void {
        $stripeObject = Util::convertToStripeObject([
            "id" => "il_123",
            "object" => "line_item",
            "amount" => 2000,
            "currency" => "usd",
            "description" => "1 x Test Subscription (at $20.00 / month)",
            "quantity" => 1,
            "unit_amount" => 2000,
            "proration" => false,
            "subscription" => "sub_123",
            "price" => [
                "id" => "price_123",
                "object" => "price",
                "product" => "prod_123",
            ],
            "metadata" => ["key" => "value"],
        ], []);

        $lineItem = StripeInvoiceLineItem::fromStripeObject($stripeObject);

        expect($lineItem)
            ->toBeInstanceOf(StripeInvoiceLineItem::class)
            ->and($lineItem->id())->toBe("il_123")
            ->and($lineItem->amount())->toBe(2000)
            ->and($lineItem->currency())->toBe("usd")
            ->and($lineItem->description())->toBe("1 x Test Subscription (at \$20.00 / month)")
            ->and($lineItem->quantity())->toBe(1)
            ->and($lineItem->unitAmount())->toBe(2000)
            ->and($lineItem->proration())->toBeFalse()
            ->and($lineItem->subscriptionId())->toBe("sub_123")
            ->and($lineItem->priceId())->toBe("price_123")
            ->and($lineItem->productId())->toBe("prod_123")
            ->and($lineItem->metadata())->toBe(["key" => "value"]);
    });

    test("handles string subscription ID", function (): void {
        $stripeObject = Util::convertToStripeObject([
            "id" => "il_123",
            "object" => "line_item",
            "subscription" => "sub_string",
            "metadata" => [],
        ], []);

        $lineItem = StripeInvoiceLineItem::fromStripeObject($stripeObject);

        expect($lineItem->subscriptionId())->toBe("sub_string");
    });

    test("handles nested subscription object", function (): void {
        $stripeObject = Util::convertToStripeObject([
            "id" => "il_123",
            "object" => "line_item",
            "subscription" => [
                "id" => "sub_nested",
                "object" => "subscription",
            ],
            "metadata" => [],
        ], []);

        $lineItem = StripeInvoiceLineItem::fromStripeObject($stripeObject);

        expect($lineItem->subscriptionId())->toBe("sub_nested");
    });

    test("extracts price and product IDs from nested price object", function (): void {
        $stripeObject = Util::convertToStripeObject([
            "id" => "il_123",
            "object" => "line_item",
            "price" => [
                "id" => "price_abc",
                "object" => "price",
                "product" => "prod_xyz",
            ],
            "metadata" => [],
        ], []);

        $lineItem = StripeInvoiceLineItem::fromStripeObject($stripeObject);

        expect($lineItem->priceId())->toBe("price_abc")
            ->and($lineItem->productId())->toBe("prod_xyz");
    });

    test("toArray returns correct structure", function (): void {
        $lineItem = StripeInvoiceLineItem::make()
            ->withId("il_123")
            ->withDescription("Test Line Item")
            ->withCurrency("usd")
            ->withAmount(1000)
            ->withQuantity(1)
            ->withUnitAmount(1000)
            ->withPriceId("price_123")
            ->withProductId("prod_123")
            ->withSubscriptionId("sub_123")
            ->withProration(false)
            ->withMetadata(["key" => "value"]);

        $array = $lineItem->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveKey("id")
            ->and($array["id"])->toBe("il_123")
            ->and($array)->toHaveKey("description")
            ->and($array)->toHaveKey("currency")
            ->and($array)->toHaveKey("amount")
            ->and($array)->toHaveKey("quantity")
            ->and($array)->toHaveKey("unit_amount")
            ->and($array)->toHaveKey("price_id")
            ->and($array)->toHaveKey("product_id")
            ->and($array)->toHaveKey("subscription")
            ->and($array)->toHaveKey("proration")
            ->and($array)->toHaveKey("metadata");
    });

    test("toArray filters null values", function (): void {
        $lineItem = StripeInvoiceLineItem::make()
            ->withId("il_123");

        $array = $lineItem->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveKey("id")
            ->and($array)->not->toHaveKey("description")
            ->and($array)->not->toHaveKey("amount")
            ->and($array)->not->toHaveKey("currency");
    });

    test("can round trip from Stripe object to array", function (): void {
        $originalData = StripeFixtures::invoiceLineItem([
            "id" => "il_123",
            "amount" => 2000,
            "description" => "Test Item",
        ]);

        $stripeObject = Util::convertToStripeObject($originalData, []);
        $lineItem = StripeInvoiceLineItem::fromStripeObject($stripeObject);
        $array = $lineItem->toArray();

        expect($array)->toHaveKey("id")
            ->and($array["id"])->toBe("il_123")
            ->and($array)->toHaveKey("amount")
            ->and($array["amount"])->toBe(2000)
            ->and($array)->toHaveKey("description")
            ->and($array["description"])->toBe("Test Item");
    });
});
