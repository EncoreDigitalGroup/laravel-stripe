<?php

use EncoreDigitalGroup\Stripe\Enums\InvoiceBillingReason;

test("InvoiceBillingReason has correct values", function (): void {
    expect(InvoiceBillingReason::AutomaticPendingInvoiceItemInvoice->value)->toBe("automatic_pending_invoice_item_invoice")
        ->and(InvoiceBillingReason::Manual->value)->toBe("manual")
        ->and(InvoiceBillingReason::QuoteAccept->value)->toBe("quote_accept")
        ->and(InvoiceBillingReason::Subscription->value)->toBe("subscription")
        ->and(InvoiceBillingReason::SubscriptionCreate->value)->toBe("subscription_create")
        ->and(InvoiceBillingReason::SubscriptionCycle->value)->toBe("subscription_cycle")
        ->and(InvoiceBillingReason::SubscriptionThreshold->value)->toBe("subscription_threshold")
        ->and(InvoiceBillingReason::SubscriptionUpdate->value)->toBe("subscription_update")
        ->and(InvoiceBillingReason::Upcoming->value)->toBe("upcoming");
});

test("InvoiceBillingReason can be created from string value", function (): void {
    expect(InvoiceBillingReason::from("subscription_cycle"))->toBe(InvoiceBillingReason::SubscriptionCycle)
        ->and(InvoiceBillingReason::from("manual"))->toBe(InvoiceBillingReason::Manual);
});
