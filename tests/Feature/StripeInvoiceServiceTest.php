<?php

use EncoreDigitalGroup\Stripe\Objects\Invoice\StripeInvoice;
use EncoreDigitalGroup\Stripe\Services\StripeInvoiceService;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

describe("StripeInvoiceService", function (): void {
    test("can retrieve an invoice", function (): void {
        $fake = Stripe::fake([
            StripeMethod::InvoicesRetrieve->value => StripeFixtures::invoice([
                "id" => "in_test123",
                "number" => "INV-001",
                "status" => "paid",
                "total" => 2000,
            ]),
        ]);

        $service = StripeInvoiceService::make();
        $invoice = $service->get("in_test123");

        expect($invoice)
            ->toBeInstanceOf(StripeInvoice::class)
            ->and($invoice->id())->toBe("in_test123")
            ->and($invoice->number())->toBe("INV-001")
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::InvoicesRetrieve);
    });

    test("can list invoices", function (): void {
        $fake = Stripe::fake([
            StripeMethod::InvoicesAll->value => StripeFixtures::invoiceList([
                StripeFixtures::invoice(["id" => "in_1", "number" => "INV-001"]),
                StripeFixtures::invoice(["id" => "in_2", "number" => "INV-002"]),
            ]),
        ]);

        $service = StripeInvoiceService::make();
        $invoices = $service->list(["limit" => 10]);

        expect($invoices)
            ->toHaveCount(2)
            ->and($invoices->first())->toBeInstanceOf(StripeInvoice::class)
            ->and($invoices->first()->id())->toBe("in_1")
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::InvoicesAll);
    });

    test("can list invoices filtered by customer", function (): void {
        $fake = Stripe::fake([
            StripeMethod::InvoicesAll->value => StripeFixtures::invoiceList([
                StripeFixtures::invoice(["id" => "in_1", "customer" => "cus_123"]),
            ]),
        ]);

        $service = StripeInvoiceService::make();
        $invoices = $service->list(["customer" => "cus_123"]);

        expect($invoices)->toHaveCount(1)
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::InvoicesAll);
    });

    test("can list invoices filtered by subscription", function (): void {
        $fake = Stripe::fake([
            StripeMethod::InvoicesAll->value => StripeFixtures::invoiceList([
                StripeFixtures::invoice(["id" => "in_1", "subscription" => "sub_123"]),
            ]),
        ]);

        $service = StripeInvoiceService::make();
        $invoices = $service->list(["subscription" => "sub_123"]);

        expect($invoices)->toHaveCount(1)
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::InvoicesAll);
    });

    test("can use wildcard pattern for invoice methods", function (): void {
        $fake = Stripe::fake([
            StripeMethod::InvoicesAny->value => StripeFixtures::invoice([
                "id" => "in_wildcard",
            ]),
        ]);

        $service = StripeInvoiceService::make();
        $invoice = $service->get("in_any");

        expect($invoice->id())->toBe("in_wildcard");
    });
});
