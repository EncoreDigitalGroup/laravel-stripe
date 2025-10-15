<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Support\StripeWebhook;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\WebhookBuilder;

describe("WebhookBuilder", function (): void {
    test("can build a basic webhook", function (): void {
        $builder = new WebhookBuilder;
        $webhook = $builder->build(
            url: "https://api.example.com/webhooks/stripe"
        );

        expect($webhook)
            ->toBeInstanceOf(StripeWebhook::class)
            ->and($webhook->url)->toBe("https://api.example.com/webhooks/stripe");
    });

    test("can build webhook with all parameters", function (): void {
        $builder = new WebhookBuilder;
        $webhook = $builder->build(
            url: "https://api.example.com/webhooks/stripe",
            events: ["customer.created", "payment_intent.succeeded"]
        );

        expect($webhook)
            ->toBeInstanceOf(StripeWebhook::class)
            ->and($webhook->url)->toBe("https://api.example.com/webhooks/stripe")
            ->and($webhook->events)->toBe(["customer.created", "payment_intent.succeeded"]);
    });

    test("can build webhook with minimal parameters", function (): void {
        $builder = new WebhookBuilder;
        $webhook = $builder->build(
            url: "https://test.example.com/stripe",
            events: ["*"]
        );

        expect($webhook)
            ->toBeInstanceOf(StripeWebhook::class)
            ->and($webhook->url)->toBe("https://test.example.com/stripe")
            ->and($webhook->events)->toBe(["*"]);
    });
});