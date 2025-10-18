<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Support\StripeWebhook;
use Illuminate\Support\Facades\Request;

test("can create webhook with url and events", function (): void {
    $webhook = StripeWebhook::make()
        ->withUrl("https://example.com/webhook")
        ->withEvents(["customer.created", "invoice.paid"]);

    expect($webhook->url())->toBe("https://example.com/webhook")
        ->and($webhook->events())->toBe(["customer.created", "invoice.paid"]);
});

test("toArray returns correct structure", function (): void {
    $webhook = StripeWebhook::make()
        ->withUrl("https://example.com/webhook")
        ->withEvents(["customer.created"]);

    $array = $webhook->toArray();

    expect($array)->toBe([
        "enabled_events" => ["customer.created"],
        "url" => "https://example.com/webhook",
    ]);
});

test("getWebhookSignatureHeader returns stripe signature from request", function (): void {
    // Set a header value in the request
    request()->headers->set("stripe-signature", "test_signature_value");

    $signature = StripeWebhook::getWebhookSignatureHeader();

    expect($signature)->toBe("test_signature_value");
});

test("fromRequest constructs event from webhook payload", function (): void {
    $payload = json_encode([
        "id" => "evt_test",
        "type" => "customer.created",
        "data" => [
            "object" => [
                "id" => "cus_test",
                "email" => "test@example.com",
            ],
        ],
    ]);

    $secret = "whsec_test";

    // Generate a valid signature for testing
    $timestamp = time();
    $signedPayload = $timestamp . "." . $payload;
    $signature = hash_hmac("sha256", $signedPayload, $secret);
    $header = "t={$timestamp},v1={$signature}";

    $event = StripeWebhook::fromRequest($payload, $header, $secret);

    expect($event)->toBeInstanceOf(\Stripe\Event::class)
        ->and($event->type)->toBe("customer.created");
});