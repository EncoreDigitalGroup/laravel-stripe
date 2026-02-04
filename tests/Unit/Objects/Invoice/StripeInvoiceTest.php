<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Enums\CollectionMethod;
use EncoreDigitalGroup\Stripe\Enums\InvoiceBillingReason;
use EncoreDigitalGroup\Stripe\Enums\InvoiceStatus;
use EncoreDigitalGroup\Stripe\Objects\Invoice\StripeInvoice;
use EncoreDigitalGroup\Stripe\Objects\Invoice\StripeInvoiceLineItem;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use Stripe\Util\Util;

describe("StripeInvoice", function (): void {
    test("can create StripeInvoice using make method", function (): void {
        $invoice = StripeInvoice::make()
            ->withId("in_123")
            ->withNumber("INV-001")
            ->withStatus(InvoiceStatus::Paid)
            ->withTotal(2000);

        expect($invoice)
            ->toBeInstanceOf(StripeInvoice::class)
            ->and($invoice->id())->toBe("in_123")
            ->and($invoice->number())->toBe("INV-001")
            ->and($invoice->status())->toBe(InvoiceStatus::Paid)
            ->and($invoice->total())->toBe(2000);
    });

    test("can create StripeInvoice from Stripe object", function (): void {
        $stripeObject = Util::convertToStripeObject([
            "id" => "in_123",
            "object" => "invoice",
            "number" => "INV-001",
            "customer" => "cus_123",
            "subscription" => "sub_123",
            "status" => "paid",
            "billing_reason" => "subscription_cycle",
            "collection_method" => "charge_automatically",
            "currency" => "usd",
            "amount_due" => 2000,
            "amount_paid" => 2000,
            "amount_remaining" => 0,
            "subtotal" => 2000,
            "total" => 2000,
            "paid" => true,
            "attempted" => true,
            "attempt_count" => 1,
            "hosted_invoice_url" => "https://invoice.stripe.com/i/test",
            "invoice_pdf" => "https://pay.stripe.com/invoice/test/pdf",
            "payment_intent" => "pi_123",
            "created" => 1234567890,
            "due_date" => 1234657890,
            "period_start" => 1234567890,
            "period_end" => 1237159890,
            "metadata" => ["key" => "value"],
            "lines" => [
                "object" => "list",
                "data" => [],
            ],
        ], []);

        $invoice = StripeInvoice::fromStripeObject($stripeObject);

        expect($invoice)
            ->toBeInstanceOf(StripeInvoice::class)
            ->and($invoice->id())->toBe("in_123")
            ->and($invoice->number())->toBe("INV-001")
            ->and($invoice->customer())->toBe("cus_123")
            ->and($invoice->subscription())->toBe("sub_123")
            ->and($invoice->status())->toBe(InvoiceStatus::Paid)
            ->and($invoice->billingReason())->toBe(InvoiceBillingReason::SubscriptionCycle)
            ->and($invoice->collectionMethod())->toBe(CollectionMethod::ChargeAutomatically)
            ->and($invoice->currency())->toBe("usd")
            ->and($invoice->amountDue())->toBe(2000)
            ->and($invoice->amountPaid())->toBe(2000)
            ->and($invoice->amountRemaining())->toBe(0)
            ->and($invoice->subtotal())->toBe(2000)
            ->and($invoice->total())->toBe(2000)
            ->and($invoice->paid())->toBeTrue()
            ->and($invoice->attempted())->toBeTrue()
            ->and($invoice->attemptCount())->toBe(1)
            ->and($invoice->hostedInvoiceUrl())->toBe("https://invoice.stripe.com/i/test")
            ->and($invoice->invoicePdf())->toBe("https://pay.stripe.com/invoice/test/pdf")
            ->and($invoice->paymentIntent())->toBe("pi_123")
            ->and($invoice->created())->toBeInstanceOf(CarbonImmutable::class)
            ->and($invoice->dueDate())->toBeInstanceOf(CarbonImmutable::class)
            ->and($invoice->periodStart())->toBeInstanceOf(CarbonImmutable::class)
            ->and($invoice->periodEnd())->toBeInstanceOf(CarbonImmutable::class)
            ->and($invoice->metadata())->toBe(["key" => "value"]);
    });

    test("handles string customer ID", function (): void {
        $stripeObject = Util::convertToStripeObject([
            "id" => "in_123",
            "object" => "invoice",
            "customer" => "cus_123",
            "metadata" => [],
            "lines" => ["object" => "list", "data" => []],
        ], []);

        $invoice = StripeInvoice::fromStripeObject($stripeObject);

        expect($invoice->customer())->toBe("cus_123");
    });

    test("handles nested customer object", function (): void {
        $stripeObject = Util::convertToStripeObject([
            "id" => "in_123",
            "object" => "invoice",
            "customer" => [
                "id" => "cus_nested",
                "object" => "customer",
            ],
            "metadata" => [],
            "lines" => ["object" => "list", "data" => []],
        ], []);

        $invoice = StripeInvoice::fromStripeObject($stripeObject);

        expect($invoice->customer())->toBe("cus_nested");
    });

    test("handles string subscription ID", function (): void {
        $stripeObject = Util::convertToStripeObject([
            "id" => "in_123",
            "object" => "invoice",
            "subscription" => "sub_123",
            "metadata" => [],
            "lines" => ["object" => "list", "data" => []],
        ], []);

        $invoice = StripeInvoice::fromStripeObject($stripeObject);

        expect($invoice->subscription())->toBe("sub_123");
    });

    test("handles nested subscription object", function (): void {
        $stripeObject = Util::convertToStripeObject([
            "id" => "in_123",
            "object" => "invoice",
            "subscription" => [
                "id" => "sub_nested",
                "object" => "subscription",
            ],
            "metadata" => [],
            "lines" => ["object" => "list", "data" => []],
        ], []);

        $invoice = StripeInvoice::fromStripeObject($stripeObject);

        expect($invoice->subscription())->toBe("sub_nested");
    });

    test("handles string payment_intent ID", function (): void {
        $stripeObject = Util::convertToStripeObject([
            "id" => "in_123",
            "object" => "invoice",
            "payment_intent" => "pi_123",
            "metadata" => [],
            "lines" => ["object" => "list", "data" => []],
        ], []);

        $invoice = StripeInvoice::fromStripeObject($stripeObject);

        expect($invoice->paymentIntent())->toBe("pi_123");
    });

    test("handles nested payment_intent object", function (): void {
        $stripeObject = Util::convertToStripeObject([
            "id" => "in_123",
            "object" => "invoice",
            "payment_intent" => [
                "id" => "pi_nested",
                "object" => "payment_intent",
            ],
            "metadata" => [],
            "lines" => ["object" => "list", "data" => []],
        ], []);

        $invoice = StripeInvoice::fromStripeObject($stripeObject);

        expect($invoice->paymentIntent())->toBe("pi_nested");
    });

    test("extracts invoice lines correctly", function (): void {
        $stripeObject = Util::convertToStripeObject([
            "id" => "in_123",
            "object" => "invoice",
            "metadata" => [],
            "lines" => [
                "object" => "list",
                "data" => [
                    [
                        "id" => "il_1",
                        "object" => "line_item",
                        "amount" => 1000,
                        "currency" => "usd",
                        "description" => "Line Item 1",
                        "quantity" => 1,
                        "unit_amount" => 1000,
                        "metadata" => [],
                    ],
                    [
                        "id" => "il_2",
                        "object" => "line_item",
                        "amount" => 500,
                        "currency" => "usd",
                        "description" => "Line Item 2",
                        "quantity" => 2,
                        "unit_amount" => 250,
                        "metadata" => [],
                    ],
                ],
            ],
        ], []);

        $invoice = StripeInvoice::fromStripeObject($stripeObject);

        expect($invoice->lines())
            ->toHaveCount(2)
            ->and($invoice->lines()->first())->toBeInstanceOf(StripeInvoiceLineItem::class)
            ->and($invoice->lines()->first()->id())->toBe("il_1")
            ->and($invoice->lines()->first()->amount())->toBe(1000)
            ->and($invoice->lines()->last()->id())->toBe("il_2");
    });

    test("toArray returns correct structure", function (): void {
        $invoice = StripeInvoice::make()
            ->withId("in_123")
            ->withNumber("INV-001")
            ->withCustomer("cus_123")
            ->withSubscription("sub_123")
            ->withStatus(InvoiceStatus::Paid)
            ->withBillingReason(InvoiceBillingReason::SubscriptionCycle)
            ->withCollectionMethod(CollectionMethod::ChargeAutomatically)
            ->withCurrency("usd")
            ->withAmountDue(2000)
            ->withAmountPaid(2000)
            ->withAmountRemaining(0)
            ->withSubtotal(2000)
            ->withTotal(2000)
            ->withPaid(true)
            ->withAttempted(true)
            ->withAttemptCount(1);

        $array = $invoice->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveKey("id")
            ->and($array["id"])->toBe("in_123")
            ->and($array)->toHaveKey("number")
            ->and($array["number"])->toBe("INV-001")
            ->and($array)->toHaveKey("customer")
            ->and($array)->toHaveKey("subscription")
            ->and($array)->toHaveKey("status")
            ->and($array["status"])->toBe("paid")
            ->and($array)->toHaveKey("billing_reason")
            ->and($array["billing_reason"])->toBe("subscription_cycle")
            ->and($array)->toHaveKey("collection_method")
            ->and($array["collection_method"])->toBe("charge_automatically")
            ->and($array)->toHaveKey("currency")
            ->and($array)->toHaveKey("amount_due")
            ->and($array)->toHaveKey("amount_paid")
            ->and($array)->toHaveKey("amount_remaining")
            ->and($array)->toHaveKey("subtotal")
            ->and($array)->toHaveKey("total")
            ->and($array)->toHaveKey("paid")
            ->and($array)->toHaveKey("attempted")
            ->and($array)->toHaveKey("attempt_count");
    });

    test("toArray filters null values", function (): void {
        $invoice = StripeInvoice::make()
            ->withId("in_123");

        $array = $invoice->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveKey("id")
            ->and($array)->not->toHaveKey("number")
            ->and($array)->not->toHaveKey("customer")
            ->and($array)->not->toHaveKey("status");
    });

    test("can round trip from Stripe object to array", function (): void {
        $originalData = StripeFixtures::invoice([
            "id" => "in_123",
            "number" => "INV-001",
            "status" => "paid",
            "total" => 2000,
        ]);

        $stripeObject = Util::convertToStripeObject($originalData, []);
        $invoice = StripeInvoice::fromStripeObject($stripeObject);
        $array = $invoice->toArray();

        expect($array)->toHaveKey("id")
            ->and($array["id"])->toBe("in_123")
            ->and($array)->toHaveKey("number")
            ->and($array["number"])->toBe("INV-001")
            ->and($array)->toHaveKey("status")
            ->and($array["status"])->toBe("paid")
            ->and($array)->toHaveKey("total")
            ->and($array["total"])->toBe(2000);
    });
});
