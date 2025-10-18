<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Webhook\StripeWebhookEndpoint;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

describe("StripeWebhookEndpoint", function () {
    describe("fluent setters and getters", function () {
        test("sets and gets url", function () {
            $endpoint = StripeWebhookEndpoint::make()
                ->withUrl("https://example.com/webhook");

            expect($endpoint->url())->toBe("https://example.com/webhook");
        });

        test("sets and gets enabled events", function () {
            $events = ["customer.created", "customer.updated", "invoice.paid"];
            $endpoint = StripeWebhookEndpoint::make()
                ->withEnabledEvents($events);

            expect($endpoint->enabledEvents())->toBe($events);
        });

        test("sets and gets description", function () {
            $endpoint = StripeWebhookEndpoint::make()
                ->withDescription("Production webhook");

            expect($endpoint->description())->toBe("Production webhook");
        });

        test("sets and gets disabled", function () {
            $endpoint = StripeWebhookEndpoint::make()
                ->withDisabled(true);

            expect($endpoint->disabled())->toBe(true);
        });

        test("sets and gets metadata", function () {
            $metadata = ["environment" => "production"];
            $endpoint = StripeWebhookEndpoint::make()
                ->withMetadata($metadata);

            expect($endpoint->metadata())->toBe($metadata);
        });

        test("returns null for unset properties", function () {
            $endpoint = StripeWebhookEndpoint::make();

            expect($endpoint->id())->toBeNull()
                ->and($endpoint->url())->toBeNull()
                ->and($endpoint->enabledEvents())->toBeNull()
                ->and($endpoint->description())->toBeNull()
                ->and($endpoint->disabled())->toBeNull()
                ->and($endpoint->metadata())->toBeNull()
                ->and($endpoint->secret())->toBeNull()
                ->and($endpoint->status())->toBeNull();
        });
    });

    describe("fromStripeObject", function () {
        test("converts stripe webhook endpoint to dto", function () {
            $stripeData = StripeFixtures::webhookEndpoint([
                "id" => "we_test123",
                "url" => "https://example.com/webhook",
                "enabled_events" => ["customer.*"],
                "description" => "Test webhook",
                "disabled" => false,
                "livemode" => false,
                "metadata" => ["key" => "value"],
                "secret" => "whsec_test",
                "status" => "enabled",
                "created" => 1234567890,
            ]);

            $stripeObject = \Stripe\WebhookEndpoint::constructFrom($stripeData);
            $endpoint = StripeWebhookEndpoint::fromStripeObject($stripeObject);

            expect($endpoint->id())->toBe("we_test123")
                ->and($endpoint->url())->toBe("https://example.com/webhook")
                ->and($endpoint->enabledEvents())->toBe(["customer.*"])
                ->and($endpoint->description())->toBe("Test webhook")
                ->and($endpoint->disabled())->toBe(false)
                ->and($endpoint->livemode())->toBe(false)
                ->and($endpoint->metadata())->toBe(["key" => "value"])
                ->and($endpoint->secret())->toBe("whsec_test")
                ->and($endpoint->status())->toBe("enabled")
                ->and($endpoint->created())->toBeInstanceOf(\Carbon\CarbonImmutable::class)
                ->and($endpoint->created()->timestamp)->toBe(1234567890);
        });

        test("handles missing optional fields", function () {
            $stripeData = StripeFixtures::webhookEndpoint([
                "id" => "we_test123",
                "url" => "https://example.com/webhook",
                "enabled_events" => ["customer.created"],
                "description" => null,
                "metadata" => [],
            ]);
            unset($stripeData["description"]);

            $stripeObject = \Stripe\WebhookEndpoint::constructFrom($stripeData);
            $endpoint = StripeWebhookEndpoint::fromStripeObject($stripeObject);

            expect($endpoint->id())->toBe("we_test123")
                ->and($endpoint->url())->toBe("https://example.com/webhook")
                ->and($endpoint->description())->toBeNull();
        });
    });

    describe("toArray", function () {
        test("converts dto to array", function () {
            $endpoint = StripeWebhookEndpoint::make()
                ->withUrl("https://example.com/webhook")
                ->withEnabledEvents(["customer.*", "invoice.*"])
                ->withDescription("Test webhook");

            $array = $endpoint->toArray();

            expect($array)
                ->toHaveKey("url", "https://example.com/webhook")
                ->toHaveKey("enabled_events", ["customer.*", "invoice.*"])
                ->toHaveKey("description", "Test webhook");
        });

        test("filters null values", function () {
            $endpoint = StripeWebhookEndpoint::make()
                ->withUrl("https://example.com/webhook");

            $array = $endpoint->toArray();

            expect($array)
                ->toHaveKey("url")
                ->not->toHaveKey("description")
                ->not->toHaveKey("metadata")
                ->not->toHaveKey("disabled");
        });
    });

    describe("save", function () {
        test("creates new webhook endpoint when id is null", function () {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsCreate->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_created123",
                ]),
            ]);

            $endpoint = StripeWebhookEndpoint::make()
                ->withUrl("https://example.com/webhook")
                ->withEnabledEvents(["customer.*"]);

            $result = $endpoint->save();

            expect($result->id())->toBe("we_created123")
                ->and($fake)->toHaveCalledStripeMethod(StripeMethod::WebhookEndpointsCreate);
        });

        test("updates existing webhook endpoint when id is set", function () {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsUpdate->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_existing123",
                    "description" => "Updated",
                ]),
            ]);

            $endpoint = StripeWebhookEndpoint::make()
                ->withId("we_existing123")
                ->withDescription("Updated");

            $result = $endpoint->save();

            expect($result->description())->toBe("Updated")
                ->and($fake)->toHaveCalledStripeMethod(StripeMethod::WebhookEndpointsUpdate);
        });
    });

    describe("get", function () {
        test("retrieves webhook endpoint by id", function () {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsRetrieve->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_test123",
                ]),
            ]);

            $endpoint = StripeWebhookEndpoint::make();
            $result = $endpoint->get("we_test123");

            expect($result->id())->toBe("we_test123")
                ->and($fake)->toHaveCalledStripeMethod(StripeMethod::WebhookEndpointsRetrieve);
        });
    });

    describe("delete", function () {
        test("deletes webhook endpoint", function () {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsDelete->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_test123",
                ]),
            ]);

            $endpoint = StripeWebhookEndpoint::make()
                ->withId("we_test123");

            $result = $endpoint->delete();

            expect($result->id())->toBe("we_test123")
                ->and($fake)->toHaveCalledStripeMethod(StripeMethod::WebhookEndpointsDelete);
        });

        test("returns self when id is null", function () {
            $endpoint = StripeWebhookEndpoint::make();
            $result = $endpoint->delete();

            expect($result)->toBe($endpoint);
        });
    });
});
