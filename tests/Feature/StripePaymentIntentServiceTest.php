<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Enums\PaymentIntentCaptureMethod;
use EncoreDigitalGroup\Stripe\Enums\PaymentIntentStatus;
use EncoreDigitalGroup\Stripe\Enums\PaymentMethodType;
use EncoreDigitalGroup\Stripe\Objects\Payment\StripePaymentIntent;
use EncoreDigitalGroup\Stripe\Services\StripePaymentIntentService;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

describe("StripePaymentIntentService", function (): void {
    test("can create a payment intent", function (): void {
        $fake = Stripe::fake([
            StripeMethod::PaymentIntentsCreate->value => StripeFixtures::paymentIntent([
                "id" => "pi_test123",
                "amount" => 2000,
                "currency" => "usd",
                "customer" => "cus_123",
                "status" => "requires_payment_method",
            ]),
        ]);

        $paymentIntent = StripePaymentIntent::make()
            ->withAmount(2000)
            ->withCurrency("usd")
            ->withCustomer("cus_123")
            ->withPaymentMethodTypes(collect([PaymentMethodType::Card]));

        $service = StripePaymentIntentService::make();
        $result = $service->create($paymentIntent);

        expect($result)
            ->toBeInstanceOf(StripePaymentIntent::class)
            ->and($result->id())->toBe("pi_test123")
            ->and($result->amount())->toBe(2000)
            ->and($result->currency())->toBe("usd")
            ->and($result->customer())->toBe("cus_123")
            ->and($result->status())->toBe(PaymentIntentStatus::RequiresPaymentMethod)
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::PaymentIntentsCreate);
    });

    test("can retrieve a payment intent", function (): void {
        $fake = Stripe::fake([
            "paymentIntents.retrieve" => StripeFixtures::paymentIntent([
                "id" => "pi_existing",
                "amount" => 1500,
            ]),
        ]);

        $service = StripePaymentIntentService::make();
        $paymentIntent = $service->get("pi_existing");

        expect($paymentIntent)
            ->toBeInstanceOf(StripePaymentIntent::class)
            ->and($paymentIntent->id())->toBe("pi_existing")
            ->and($paymentIntent->amount())->toBe(1500)
            ->and($fake)->toHaveCalledStripeMethod("paymentIntents.retrieve");
    });

    test("can update a payment intent", function (): void {
        $fake = Stripe::fake([
            "paymentIntents.update" => StripeFixtures::paymentIntent([
                "id" => "pi_123",
                "amount" => 3000,
                "description" => "Updated description",
            ]),
        ]);

        $paymentIntent = StripePaymentIntent::make()
            ->withAmount(3000)
            ->withDescription("Updated description");

        $service = StripePaymentIntentService::make();
        $result = $service->update("pi_123", $paymentIntent);

        expect($result)
            ->toBeInstanceOf(StripePaymentIntent::class)
            ->and($result->amount())->toBe(3000)
            ->and($result->description())->toBe("Updated description")
            ->and($fake)->toHaveCalledStripeMethod("paymentIntents.update");
    });

    test("can confirm a payment intent", function (): void {
        $fake = Stripe::fake([
            "paymentIntents.confirm" => StripeFixtures::paymentIntent([
                "id" => "pi_123",
                "status" => "succeeded",
                "payment_method" => "pm_card_visa",
            ]),
        ]);

        $service = StripePaymentIntentService::make();
        $result = $service->confirm("pi_123", ["payment_method" => "pm_card_visa"]);

        expect($result)
            ->toBeInstanceOf(StripePaymentIntent::class)
            ->and($result->status())->toBe(PaymentIntentStatus::Succeeded)
            ->and($result->paymentMethod())->toBe("pm_card_visa")
            ->and($fake)->toHaveCalledStripeMethod("paymentIntents.confirm");
    });

    test("can cancel a payment intent", function (): void {
        $fake = Stripe::fake([
            "paymentIntents.cancel" => StripeFixtures::paymentIntent([
                "id" => "pi_123",
                "status" => "canceled",
                "cancellation_reason" => "requested_by_customer",
            ]),
        ]);

        $service = StripePaymentIntentService::make();
        $result = $service->cancel("pi_123", ["cancellation_reason" => "requested_by_customer"]);

        expect($result)
            ->toBeInstanceOf(StripePaymentIntent::class)
            ->and($result->status())->toBe(PaymentIntentStatus::Canceled)
            ->and($fake)->toHaveCalledStripeMethod("paymentIntents.cancel");
    });

    test("can capture a payment intent", function (): void {
        $fake = Stripe::fake([
            "paymentIntents.capture" => StripeFixtures::paymentIntent([
                "id" => "pi_123",
                "status" => "succeeded",
                "amount_received" => 2000,
            ]),
        ]);

        $service = StripePaymentIntentService::make();
        $result = $service->capture("pi_123");

        expect($result)
            ->toBeInstanceOf(StripePaymentIntent::class)
            ->and($result->status())->toBe(PaymentIntentStatus::Succeeded)
            ->and($result->amountReceived())->toBe(2000)
            ->and($fake)->toHaveCalledStripeMethod("paymentIntents.capture");
    });

    test("can list payment intents", function (): void {
        $fake = Stripe::fake([
            "paymentIntents.all" => StripeFixtures::paymentIntentList([
                StripeFixtures::paymentIntent(["id" => "pi_1"]),
                StripeFixtures::paymentIntent(["id" => "pi_2"]),
                StripeFixtures::paymentIntent(["id" => "pi_3"]),
            ]),
        ]);

        $service = StripePaymentIntentService::make();
        $paymentIntents = $service->list(["limit" => 10]);

        expect($paymentIntents)
            ->toHaveCount(3)
            ->and($paymentIntents->first())->toBeInstanceOf(StripePaymentIntent::class)
            ->and($paymentIntents->first()->id())->toBe("pi_1")
            ->and($fake)->toHaveCalledStripeMethod("paymentIntents.all");
    });

    test("can search payment intents", function (): void {
        $fake = Stripe::fake([
            "paymentIntents.search" => StripeFixtures::paymentIntentList([
                StripeFixtures::paymentIntent(["id" => "pi_1", "customer" => "cus_123"]),
                StripeFixtures::paymentIntent(["id" => "pi_2", "customer" => "cus_123"]),
            ]),
        ]);

        $service = StripePaymentIntentService::make();
        $paymentIntents = $service->search("customer:'cus_123'");

        expect($paymentIntents)
            ->toHaveCount(2)
            ->and($paymentIntents->first())->toBeInstanceOf(StripePaymentIntent::class)
            ->and($fake)->toHaveCalledStripeMethod("paymentIntents.search");
    });

    test("can create payment intent with manual capture", function (): void {
        $fake = Stripe::fake([
            "paymentIntents.create" => StripeFixtures::paymentIntent([
                "id" => "pi_manual",
                "capture_method" => "manual",
                "status" => "requires_payment_method",
            ]),
        ]);

        $paymentIntent = StripePaymentIntent::make()
            ->withAmount(5000)
            ->withCurrency("usd")
            ->withCaptureMethod(PaymentIntentCaptureMethod::Manual);

        $service = StripePaymentIntentService::make();
        $result = $service->create($paymentIntent);

        expect($result->captureMethod())->toBe(PaymentIntentCaptureMethod::Manual)
            ->and($fake)->toHaveCalledStripeMethod("paymentIntents.create");
    });

    test("can use wildcard patterns for payment intent methods", function (): void {
        Stripe::fake([
            "paymentIntents.*" => StripeFixtures::paymentIntent(["id" => "pi_wildcard"]),
        ]);

        $service = StripePaymentIntentService::make();
        $retrieved = $service->get("pi_any");
        $created = $service->create(StripePaymentIntent::make());

        expect($retrieved->id())->toBe("pi_wildcard")
            ->and($created->id())->toBe("pi_wildcard");
    });
});
