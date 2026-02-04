<?php



use EncoreDigitalGroup\Stripe\Enums\PaymentMethodType;
use EncoreDigitalGroup\Stripe\Enums\SetupIntentStatus;
use EncoreDigitalGroup\Stripe\Enums\SetupIntentUsage;
use EncoreDigitalGroup\Stripe\Objects\Payment\StripeSetupIntent;
use EncoreDigitalGroup\Stripe\Services\StripeSetupIntentService;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

describe("StripeSetupIntentService", function (): void {
    test("can create a setup intent", function (): void {
        $fake = Stripe::fake([
            StripeMethod::SetupIntentsCreate->value => StripeFixtures::setupIntent([
                "id" => "seti_test123",
                "customer" => "cus_123",
                "status" => "requires_payment_method",
                "usage" => "off_session",
            ]),
        ]);

        $setupIntent = StripeSetupIntent::make()
            ->withCustomer("cus_123")
            ->withUsage(SetupIntentUsage::OffSession)
            ->withPaymentMethodTypes(collect([PaymentMethodType::Card]));

        $service = StripeSetupIntentService::make();
        $result = $service->create($setupIntent);

        expect($result)
            ->toBeInstanceOf(StripeSetupIntent::class)
            ->and($result->id())->toBe("seti_test123")
            ->and($result->customer())->toBe("cus_123")
            ->and($result->status())->toBe(SetupIntentStatus::RequiresPaymentMethod)
            ->and($result->usage())->toBe(SetupIntentUsage::OffSession)
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::SetupIntentsCreate);
    });

    test("can retrieve a setup intent", function (): void {
        $fake = Stripe::fake([
            "setupIntents.retrieve" => StripeFixtures::setupIntent([
                "id" => "seti_existing",
                "customer" => "cus_456",
            ]),
        ]);

        $service = StripeSetupIntentService::make();
        $setupIntent = $service->get("seti_existing");

        expect($setupIntent)
            ->toBeInstanceOf(StripeSetupIntent::class)
            ->and($setupIntent->id())->toBe("seti_existing")
            ->and($setupIntent->customer())->toBe("cus_456")
            ->and($fake)->toHaveCalledStripeMethod("setupIntents.retrieve");
    });

    test("can update a setup intent", function (): void {
        $fake = Stripe::fake([
            "setupIntents.update" => StripeFixtures::setupIntent([
                "id" => "seti_123",
                "description" => "Updated description",
                "metadata" => ["key" => "value"],
            ]),
        ]);

        $setupIntent = StripeSetupIntent::make()
            ->withDescription("Updated description")
            ->withMetadata(["key" => "value"]);

        $service = StripeSetupIntentService::make();
        $result = $service->update("seti_123", $setupIntent);

        expect($result)
            ->toBeInstanceOf(StripeSetupIntent::class)
            ->and($result->description())->toBe("Updated description")
            ->and($result->metadata())->toBe(["key" => "value"])
            ->and($fake)->toHaveCalledStripeMethod("setupIntents.update");
    });

    test("can confirm a setup intent", function (): void {
        $fake = Stripe::fake([
            "setupIntents.confirm" => StripeFixtures::setupIntent([
                "id" => "seti_123",
                "status" => "succeeded",
                "payment_method" => "pm_card_visa",
            ]),
        ]);

        $service = StripeSetupIntentService::make();
        $result = $service->confirm("seti_123", ["payment_method" => "pm_card_visa"]);

        expect($result)
            ->toBeInstanceOf(StripeSetupIntent::class)
            ->and($result->status())->toBe(SetupIntentStatus::Succeeded)
            ->and($result->paymentMethod())->toBe("pm_card_visa")
            ->and($fake)->toHaveCalledStripeMethod("setupIntents.confirm");
    });

    test("can cancel a setup intent", function (): void {
        $fake = Stripe::fake([
            "setupIntents.cancel" => StripeFixtures::setupIntent([
                "id" => "seti_123",
                "status" => "canceled",
                "cancellation_reason" => "requested_by_customer",
            ]),
        ]);

        $service = StripeSetupIntentService::make();
        $result = $service->cancel("seti_123", ["cancellation_reason" => "requested_by_customer"]);

        expect($result)
            ->toBeInstanceOf(StripeSetupIntent::class)
            ->and($result->status())->toBe(SetupIntentStatus::Canceled)
            ->and($fake)->toHaveCalledStripeMethod("setupIntents.cancel");
    });

    test("can list setup intents", function (): void {
        $fake = Stripe::fake([
            "setupIntents.all" => StripeFixtures::setupIntentList([
                StripeFixtures::setupIntent(["id" => "seti_1"]),
                StripeFixtures::setupIntent(["id" => "seti_2"]),
                StripeFixtures::setupIntent(["id" => "seti_3"]),
            ]),
        ]);

        $service = StripeSetupIntentService::make();
        $setupIntents = $service->list(["limit" => 10]);

        expect($setupIntents)
            ->toHaveCount(3)
            ->and($setupIntents->first())->toBeInstanceOf(StripeSetupIntent::class)
            ->and($setupIntents->first()->id())->toBe("seti_1")
            ->and($fake)->toHaveCalledStripeMethod("setupIntents.all");
    });

    test("can create setup intent with description and metadata", function (): void {
        $fake = Stripe::fake([
            "setupIntents.create" => StripeFixtures::setupIntent([
                "id" => "seti_meta",
                "customer" => "cus_123",
                "description" => "Save payment method for subscription",
                "metadata" => ["user_id" => "123", "plan" => "premium"],
            ]),
        ]);

        $setupIntent = StripeSetupIntent::make()
            ->withCustomer("cus_123")
            ->withDescription("Save payment method for subscription")
            ->withMetadata(["user_id" => "123", "plan" => "premium"]);

        $service = StripeSetupIntentService::make();
        $result = $service->create($setupIntent);

        expect($result->description())->toBe("Save payment method for subscription")
            ->and($result->metadata())->toBe(["user_id" => "123", "plan" => "premium"])
            ->and($fake)->toHaveCalledStripeMethod("setupIntents.create");
    });

    test("can create setup intent with on_session usage", function (): void {
        $fake = Stripe::fake([
            "setupIntents.create" => StripeFixtures::setupIntent([
                "id" => "seti_on_session",
                "usage" => "on_session",
            ]),
        ]);

        $setupIntent = StripeSetupIntent::make()
            ->withCustomer("cus_123")
            ->withUsage(SetupIntentUsage::OnSession);

        $service = StripeSetupIntentService::make();
        $result = $service->create($setupIntent);

        expect($result->usage())->toBe(SetupIntentUsage::OnSession)
            ->and($fake)->toHaveCalledStripeMethod("setupIntents.create");
    });

    test("can use wildcard patterns for setup intent methods", function (): void {
        Stripe::fake([
            "setupIntents.*" => StripeFixtures::setupIntent(["id" => "seti_wildcard"]),
        ]);

        $service = StripeSetupIntentService::make();
        $retrieved = $service->get("seti_any");
        $created = $service->create(StripeSetupIntent::make());

        expect($retrieved->id())->toBe("seti_wildcard")
            ->and($created->id())->toBe("seti_wildcard");
    });
});
