<?php



use EncoreDigitalGroup\Stripe\Objects\Webhook\StripeWebhookEndpoint;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

describe("Webhook Endpoint Integration", function (): void {
    describe("Stripe facade shortcuts", function (): void {
        test("creates webhook endpoint via Stripe facade shortcut", function (): void {
            $endpoint = Stripe::webhook();

            expect($endpoint)->toBeInstanceOf(StripeWebhookEndpoint::class);
        });

        test("accesses service via Stripe facade", function (): void {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsAll->value => StripeFixtures::webhookEndpointList(),
            ]);

            $service = Stripe::webhook()->service();
            $service->list();

            expect($fake)->toHaveCalledStripeMethod(StripeMethod::WebhookEndpointsAll);
        });
    });

    describe("fluent workflow", function (): void {
        test("creates and updates webhook endpoint fluently", function (): void {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsCreate->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_new123",
                    "url" => "https://example.com/webhook",
                    "enabled_events" => ["customer.created"],
                ]),
                StripeMethod::WebhookEndpointsUpdate->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_new123",
                    "url" => "https://example.com/webhook",
                    "enabled_events" => ["customer.*"],
                ]),
            ]);

            $endpoint = Stripe::webhook()
                ->withUrl("https://example.com/webhook")
                ->withEnabledEvents(["customer.created"])
                ->save();

            expect($endpoint->id())->toBe("we_new123")
                ->and($endpoint->enabledEvents())->toBe(["customer.created"]);

            $updated = $endpoint
                ->withEnabledEvents(["customer.*"])
                ->save();

            expect($updated->enabledEvents())->toBe(["customer.*"])
                ->and($fake)->toHaveCalledStripeMethodTimes(StripeMethod::WebhookEndpointsCreate, 1)
                ->and($fake)->toHaveCalledStripeMethodTimes(StripeMethod::WebhookEndpointsUpdate, 1);
        });

        test("retrieves and deletes webhook endpoint fluently", function (): void {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsRetrieve->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_test123",
                ]),
                StripeMethod::WebhookEndpointsDelete->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_test123",
                ]),
            ]);

            $endpoint = Stripe::webhook()->get("we_test123");

            expect($endpoint->id())->toBe("we_test123");

            $endpoint->delete();

            expect($fake)->toHaveCalledStripeMethod(StripeMethod::WebhookEndpointsDelete);
        });
    });

    describe("complete workflow example", function (): void {
        test("demonstrates full webhook endpoint lifecycle", function (): void {
            $fake = Stripe::fake([
                StripeMethod::WebhookEndpointsCreate->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_lifecycle123",
                    "url" => "https://example.com/webhook",
                    "enabled_events" => ["customer.created"],
                    "secret" => "whsec_secret123",
                ]),
                StripeMethod::WebhookEndpointsUpdate->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_lifecycle123",
                    "url" => "https://example.com/webhook",
                    "enabled_events" => ["customer.*", "invoice.*"],
                    "description" => "Updated webhook",
                    "secret" => "whsec_secret123",
                ]),
                StripeMethod::WebhookEndpointsRetrieve->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_lifecycle123",
                ]),
                StripeMethod::WebhookEndpointsDelete->value => StripeFixtures::webhookEndpoint([
                    "id" => "we_lifecycle123",
                ]),
            ]);

            $endpoint = Stripe::webhook()
                ->withUrl("https://example.com/webhook")
                ->withEnabledEvents(["customer.created"])
                ->save();

            expect($endpoint->id())->toBe("we_lifecycle123")
                ->and($endpoint->secret())->toBe("whsec_secret123");

            $endpoint = $endpoint
                ->withEnabledEvents(["customer.*", "invoice.*"])
                ->withDescription("Updated webhook")
                ->save();

            expect($endpoint->description())->toBe("Updated webhook")
                ->and($endpoint->enabledEvents())->toHaveCount(2);

            $retrieved = Stripe::webhook()->get("we_lifecycle123");
            expect($retrieved->id())->toBe("we_lifecycle123");

            $retrieved->delete();

            expect($fake)->toHaveCalledStripeMethodTimes(StripeMethod::WebhookEndpointsCreate, 1)
                ->and($fake)->toHaveCalledStripeMethodTimes(StripeMethod::WebhookEndpointsUpdate, 1)
                ->and($fake)->toHaveCalledStripeMethodTimes(StripeMethod::WebhookEndpointsRetrieve, 1)
                ->and($fake)->toHaveCalledStripeMethodTimes(StripeMethod::WebhookEndpointsDelete, 1);
        });
    });
});
