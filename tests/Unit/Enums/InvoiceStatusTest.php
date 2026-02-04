<?php

use EncoreDigitalGroup\Stripe\Enums\InvoiceStatus;

test("InvoiceStatus has correct values", function (): void {
    expect(InvoiceStatus::Draft->value)->toBe("draft")
        ->and(InvoiceStatus::Open->value)->toBe("open")
        ->and(InvoiceStatus::Paid->value)->toBe("paid")
        ->and(InvoiceStatus::Uncollectible->value)->toBe("uncollectible")
        ->and(InvoiceStatus::Void->value)->toBe("void");
});

test("InvoiceStatus can be created from string value", function (): void {
    expect(InvoiceStatus::from("draft"))->toBe(InvoiceStatus::Draft)
        ->and(InvoiceStatus::from("open"))->toBe(InvoiceStatus::Open)
        ->and(InvoiceStatus::from("paid"))->toBe(InvoiceStatus::Paid)
        ->and(InvoiceStatus::from("uncollectible"))->toBe(InvoiceStatus::Uncollectible)
        ->and(InvoiceStatus::from("void"))->toBe(InvoiceStatus::Void);
});
