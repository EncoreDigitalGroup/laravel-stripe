<?php



use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Objects\Webhook\Payloads\StripeInvoiceWebhookData;
use EncoreDigitalGroup\Stripe\Objects\Webhook\Payloads\StripePaymentIntentWebhookData;
use EncoreDigitalGroup\Stripe\Objects\Webhook\StripeWebhookEvent;
use Stripe\Event as StripeEvent;

test("can create StripeWebhookEvent using fluent pattern", function (): void {
    $event = StripeWebhookEvent::make()
        ->withId("evt_123")
        ->withType("invoice.created")
        ->withLivemode(true);

    expect($event)
        ->toBeInstanceOf(StripeWebhookEvent::class)
        ->and($event->id())->toBe("evt_123")
        ->and($event->type())->toBe("invoice.created")
        ->and($event->livemode())->toBe(true);
});

test("can create StripeWebhookEvent from Stripe Event", function (): void {
    $stripeEvent = StripeEvent::constructFrom([
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
                "customer" => "cus_123",
                "created" => 1234567890,
                "lines" => [
                    "data" => [],
                ],
            ],
        ],
    ]);

    $event = StripeWebhookEvent::fromStripeEvent($stripeEvent);

    expect($event)
        ->toBeInstanceOf(StripeWebhookEvent::class)
        ->and($event->id())->toBe("evt_123")
        ->and($event->type())->toBe("invoice.created")
        ->and($event->created())->toBeInstanceOf(CarbonImmutable::class)
        ->and($event->livemode())->toBe(true)
        ->and($event->apiVersion())->toBe("2023-10-16")
        ->and($event->data())->toBeInstanceOf(StripeInvoiceWebhookData::class);
});

test("can create StripeWebhookEvent from payment intent event", function (): void {
    $stripeEvent = StripeEvent::constructFrom([
        "id" => "evt_456",
        "type" => "payment_intent.succeeded",
        "created" => 1234567890,
        "livemode" => false,
        "api_version" => "2023-10-16",
        "data" => [
            "object" => [
                "id" => "pi_123",
                "status" => "succeeded",
                "amount" => 5000,
                "amount_received" => 5000,
                "currency" => "usd",
                "customer" => "cus_123",
                "created" => 1234567890,
            ],
        ],
    ]);

    $event = StripeWebhookEvent::fromStripeEvent($stripeEvent);

    expect($event)
        ->toBeInstanceOf(StripeWebhookEvent::class)
        ->and($event->id())->toBe("evt_456")
        ->and($event->type())->toBe("payment_intent.succeeded")
        ->and($event->data())->toBeInstanceOf(StripePaymentIntentWebhookData::class);
});

test("fromStripeEvent handles unknown event types as raw array", function (): void {
    $stripeEvent = StripeEvent::constructFrom([
        "id" => "evt_789",
        "type" => "customer.created",
        "created" => 1234567890,
        "livemode" => false,
        "api_version" => "2023-10-16",
        "data" => [
            "object" => [
                "id" => "cus_123",
                "email" => "test@example.com",
                "name" => "Test Customer",
            ],
        ],
    ]);

    $event = StripeWebhookEvent::fromStripeEvent($stripeEvent);

    expect($event->type())->toBe("customer.created")
        ->and($event->data())->toBeArray()
        ->and($event->data()["id"])->toBe("cus_123");
});

test("asInvoiceData returns invoice data when event is invoice-related", function (): void {
    $stripeEvent = StripeEvent::constructFrom([
        "id" => "evt_123",
        "type" => "invoice.paid",
        "created" => 1234567890,
        "data" => [
            "object" => [
                "id" => "in_123",
                "number" => "INV-001",
                "total" => 2000,
                "status" => "paid",
                "customer" => "cus_123",
                "created" => 1234567890,
                "lines" => [
                    "data" => [],
                ],
            ],
        ],
    ]);

    $event = StripeWebhookEvent::fromStripeEvent($stripeEvent);
    $invoiceData = $event->asInvoiceData();

    expect($invoiceData)->toBeInstanceOf(StripeInvoiceWebhookData::class)
        ->and($invoiceData->id())->toBe("in_123");
});

test("asInvoiceData returns null when event is not invoice-related", function (): void {
    $stripeEvent = StripeEvent::constructFrom([
        "id" => "evt_456",
        "type" => "customer.created",
        "created" => 1234567890,
        "data" => [
            "object" => [
                "id" => "cus_123",
                "email" => "test@example.com",
            ],
        ],
    ]);

    $event = StripeWebhookEvent::fromStripeEvent($stripeEvent);

    expect($event->asInvoiceData())->toBeNull();
});

test("asPaymentIntentData returns payment intent data when event is payment intent-related", function (): void {
    $stripeEvent = StripeEvent::constructFrom([
        "id" => "evt_789",
        "type" => "payment_intent.succeeded",
        "created" => 1234567890,
        "data" => [
            "object" => [
                "id" => "pi_123",
                "status" => "succeeded",
                "amount" => 5000,
                "amount_received" => 5000,
                "currency" => "usd",
                "created" => 1234567890,
            ],
        ],
    ]);

    $event = StripeWebhookEvent::fromStripeEvent($stripeEvent);
    $paymentIntentData = $event->asPaymentIntentData();

    expect($paymentIntentData)->toBeInstanceOf(StripePaymentIntentWebhookData::class)
        ->and($paymentIntentData->id())->toBe("pi_123");
});

test("asPaymentIntentData returns null when event is not payment intent-related", function (): void {
    $stripeEvent = StripeEvent::constructFrom([
        "id" => "evt_123",
        "type" => "customer.created",
        "created" => 1234567890,
        "data" => [
            "object" => [
                "id" => "cus_123",
                "email" => "test@example.com",
            ],
        ],
    ]);

    $event = StripeWebhookEvent::fromStripeEvent($stripeEvent);

    expect($event->asPaymentIntentData())->toBeNull();
});

test("asRawData returns raw array for unknown event types", function (): void {
    $stripeEvent = StripeEvent::constructFrom([
        "id" => "evt_123",
        "type" => "customer.created",
        "created" => 1234567890,
        "data" => [
            "object" => [
                "id" => "cus_123",
                "email" => "test@example.com",
                "name" => "Test User",
            ],
        ],
    ]);

    $event = StripeWebhookEvent::fromStripeEvent($stripeEvent);
    $rawData = $event->asRawData();

    expect($rawData)->toBeArray()
        ->and($rawData["id"])->toBe("cus_123")
        ->and($rawData["email"])->toBe("test@example.com");
});

test("asRawData returns null for typed events", function (): void {
    $stripeEvent = StripeEvent::constructFrom([
        "id" => "evt_456",
        "type" => "invoice.paid",
        "created" => 1234567890,
        "data" => [
            "object" => [
                "id" => "in_123",
                "number" => "INV-001",
                "total" => 2000,
                "status" => "paid",
                "customer" => "cus_123",
                "created" => 1234567890,
                "lines" => [
                    "data" => [],
                ],
            ],
        ],
    ]);

    $event = StripeWebhookEvent::fromStripeEvent($stripeEvent);

    expect($event->asRawData())->toBeNull();
});

test("toArray returns correct structure for invoice events", function (): void {
    $event = StripeWebhookEvent::make()
        ->withId("evt_123")
        ->withType("invoice.paid")
        ->withCreated(CarbonImmutable::createFromTimestamp(1234567890))
        ->withLivemode(true)
        ->withApiVersion("2023-10-16")
        ->withData(StripeInvoiceWebhookData::make(id: "in_123"));

    $array = $event->toArray();

    expect($array)
        ->toBeArray()
        ->toHaveKey("id", "evt_123")
        ->toHaveKey("type", "invoice.paid")
        ->toHaveKey("created", 1234567890)
        ->toHaveKey("livemode", true)
        ->toHaveKey("api_version", "2023-10-16")
        ->toHaveKey("data");
});

test("toArray filters null values", function (): void {
    $event = StripeWebhookEvent::make()
        ->withId("evt_123")
        ->withType("customer.created");

    $array = $event->toArray();

    expect($array)
        ->toBeArray()
        ->toHaveKey("id")
        ->toHaveKey("type")
        ->not->toHaveKey("created")
        ->not->toHaveKey("livemode")
        ->not->toHaveKey("api_version");
});
