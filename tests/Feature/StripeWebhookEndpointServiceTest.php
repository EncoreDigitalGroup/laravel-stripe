<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Webhook\StripeWebhookEndpoint;
use EncoreDigitalGroup\Stripe\Services\StripeWebhookEndpointService;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

describe("StripeWebhookEndpointService", function (): void {
    describe("create", function (): void {
        test("creates webhook endpoint via service", function (): void {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsCreate->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_test123",
                    "url" => "https://example.com/webhook",
                    "enabled_events" => ["customer.created"],
                ]),
            ]);

            $service = StripeWebhookEndpointService::make();
            $endpoint = StripeWebhookEndpoint::make()
                ->withUrl("https://example.com/webhook")
                ->withEnabledEvents(["customer.created"]);

            $result = $service->create($endpoint);

            expect($result)
                ->toBeInstanceOf(StripeWebhookEndpoint::class)
                ->and($result->id())->toBe("we_test123")
                ->and($result->url())->toBe("https://example.com/webhook")
                ->and($result->enabledEvents())->toBe(["customer.created"])
                ->and($fake)->toHaveCalledStripeMethod(StripeMethod::WebhookEndpointsCreate);
        });

        test("removes read-only fields from create request", function (): void {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsCreate->value => StripeFixtures::webhookEndpoint(),
            ]);

            $service = StripeWebhookEndpointService::make();
            $endpoint = StripeWebhookEndpoint::make()
                ->withId("we_shouldnotbesent")
                ->withUrl("https://example.com/webhook")
                ->withEnabledEvents(["customer.*"])
                ->withSecret("whsec_shouldnotbesent")
                ->withStatus("enabled");

            $service->create($endpoint);

            $calledParams = $fake->getCall(StripeMethod::WebhookEndpointsCreate->value, 0);
            expect($calledParams)
                ->not->toHaveKey("id")
                ->not->toHaveKey("secret")
                ->not->toHaveKey("status")
                ->not->toHaveKey("livemode")
                ->toHaveKey("url", "https://example.com/webhook")
                ->toHaveKey("enabled_events", ["customer.*"]);
        });
    });

    describe("get", function (): void {
        test("retrieves webhook endpoint by id", function (): void {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsRetrieve->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_test123",
                    "url" => "https://example.com/webhook",
                ]),
            ]);

            $service = StripeWebhookEndpointService::make();
            $result = $service->get("we_test123");

            expect($result)
                ->toBeInstanceOf(StripeWebhookEndpoint::class)
                ->and($result->id())->toBe("we_test123")
                ->and($result->url())->toBe("https://example.com/webhook")
                ->and($fake)->toHaveCalledStripeMethod(StripeMethod::WebhookEndpointsRetrieve);
        });
    });

    describe("update", function (): void {
        test("updates webhook endpoint", function (): void {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsUpdate->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_test123",
                    "enabled_events" => ["customer.*", "invoice.*"],
                    "description" => "Updated description",
                ]),
            ]);

            $service = StripeWebhookEndpointService::make();
            $endpoint = StripeWebhookEndpoint::make()
                ->withEnabledEvents(["customer.*", "invoice.*"])
                ->withDescription("Updated description");

            $result = $service->update("we_test123", $endpoint);

            expect($result)
                ->toBeInstanceOf(StripeWebhookEndpoint::class)
                ->and($result->enabledEvents())->toBe(["customer.*", "invoice.*"])
                ->and($result->description())->toBe("Updated description")
                ->and($fake)->toHaveCalledStripeMethod(StripeMethod::WebhookEndpointsUpdate);
        });

        test("removes read-only fields from update request", function (): void {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsUpdate->value => StripeFixtures::webhookEndpoint(),
            ]);

            $service = StripeWebhookEndpointService::make();
            $endpoint = StripeWebhookEndpoint::make()
                ->withId("we_test123")
                ->withUrl("https://example.com/webhook")
                ->withSecret("whsec_shouldnotbesent")
                ->withLivemode(true);

            $service->update("we_test123", $endpoint);

            $calledParams = $fake->getCall(StripeMethod::WebhookEndpointsUpdate->value, 0);
            expect($calledParams)
                ->not->toHaveKey("id")
                ->not->toHaveKey("secret")
                ->not->toHaveKey("livemode")
                ->not->toHaveKey("status");
        });
    });

    describe("delete", function (): void {
        test("deletes webhook endpoint", function (): void {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsDelete->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_test123",
                ]),
            ]);

            $service = StripeWebhookEndpointService::make();
            $result = $service->delete("we_test123");

            expect($result)
                ->toBeInstanceOf(StripeWebhookEndpoint::class)
                ->and($result->id())->toBe("we_test123")
                ->and($fake)->toHaveCalledStripeMethod(StripeMethod::WebhookEndpointsDelete);
        });
    });

    describe("list", function (): void {
        test("lists webhook endpoints", function (): void {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsAll->value => StripeFixtures::webhookEndpointList([
                    StripeFixtures::webhookEndpoint(["id" => "we_1"]),
                    StripeFixtures::webhookEndpoint(["id" => "we_2"]),
                ]),
            ]);

            $service = StripeWebhookEndpointService::make();
            $results = $service->list();

            expect($results)
                ->toHaveCount(2)
                ->and($results->first())->toBeInstanceOf(StripeWebhookEndpoint::class)
                ->and($results->first()->id())->toBe("we_1")
                ->and($results->last()->id())->toBe("we_2")
                ->and($fake)->toHaveCalledStripeMethod(StripeMethod::WebhookEndpointsAll);
        });

        test("passes params to list method", function (): void {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsAll->value => StripeFixtures::webhookEndpointList(),
            ]);

            $service = StripeWebhookEndpointService::make();
            $service->list(["limit" => 10]);

            $calledParams = $fake->getCall(StripeMethod::WebhookEndpointsAll->value, 0);
            expect($calledParams)->toHaveKey("limit", 10);
        });
    });
});
