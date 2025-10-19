<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Enums\PaymentMethodType;
use EncoreDigitalGroup\Stripe\Objects\Payment\StripePaymentMethod;
use EncoreDigitalGroup\Stripe\Services\StripePaymentMethodService;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

describe("StripePaymentMethodService", function (): void {
    test("can create a payment method", function (): void {
        $fake = Stripe::fake([
            StripeMethod::PaymentMethodsCreate->value => StripeFixtures::paymentMethod([
                "id" => "pm_test123",
                "type" => "card",
            ]),
        ]);

        $paymentMethod = StripePaymentMethod::make()
            ->withType(PaymentMethodType::Card);

        $service = StripePaymentMethodService::make();
        $result = $service->create($paymentMethod);

        expect($result)
            ->toBeInstanceOf(StripePaymentMethod::class)
            ->and($result->id())->toBe("pm_test123")
            ->and($result->type())->toBe(PaymentMethodType::Card)
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::PaymentMethodsCreate);
    });

    test("can retrieve a payment method", function (): void {
        $fake = Stripe::fake([
            "paymentMethods.retrieve" => StripeFixtures::paymentMethod([
                "id" => "pm_existing",
                "customer" => "cus_123",
            ]),
        ]);

        $service = StripePaymentMethodService::make();
        $paymentMethod = $service->get("pm_existing");

        expect($paymentMethod)
            ->toBeInstanceOf(StripePaymentMethod::class)
            ->and($paymentMethod->id())->toBe("pm_existing")
            ->and($paymentMethod->customer())->toBe("cus_123")
            ->and($fake)->toHaveCalledStripeMethod("paymentMethods.retrieve");
    });

    test("can update a payment method", function (): void {
        $fake = Stripe::fake([
            "paymentMethods.update" => StripeFixtures::paymentMethod([
                "id" => "pm_123",
                "metadata" => ["updated" => "true"],
            ]),
        ]);

        $paymentMethod = StripePaymentMethod::make()
            ->withMetadata(["updated" => "true"]);

        $service = StripePaymentMethodService::make();
        $result = $service->update("pm_123", $paymentMethod);

        expect($result)
            ->toBeInstanceOf(StripePaymentMethod::class)
            ->and($result->metadata())->toBe(["updated" => "true"])
            ->and($fake)->toHaveCalledStripeMethod("paymentMethods.update");
    });

    test("can attach a payment method to a customer", function (): void {
        $fake = Stripe::fake([
            "paymentMethods.attach" => StripeFixtures::paymentMethod([
                "id" => "pm_123",
                "customer" => "cus_456",
            ]),
        ]);

        $service = StripePaymentMethodService::make();
        $result = $service->attach("pm_123", "cus_456");

        expect($result)
            ->toBeInstanceOf(StripePaymentMethod::class)
            ->and($result->id())->toBe("pm_123")
            ->and($result->customer())->toBe("cus_456")
            ->and($fake)->toHaveCalledStripeMethod("paymentMethods.attach");
    });

    test("can detach a payment method from a customer", function (): void {
        $fake = Stripe::fake([
            "paymentMethods.detach" => StripeFixtures::paymentMethod([
                "id" => "pm_123",
                "customer" => null,
            ]),
        ]);

        $service = StripePaymentMethodService::make();
        $result = $service->detach("pm_123");

        expect($result)
            ->toBeInstanceOf(StripePaymentMethod::class)
            ->and($result->id())->toBe("pm_123")
            ->and($result->customer())->toBeNull()
            ->and($fake)->toHaveCalledStripeMethod("paymentMethods.detach");
    });

    test("can list payment methods", function (): void {
        $fake = Stripe::fake([
            "paymentMethods.all" => StripeFixtures::paymentMethodList([
                StripeFixtures::paymentMethod(["id" => "pm_1"]),
                StripeFixtures::paymentMethod(["id" => "pm_2"]),
                StripeFixtures::paymentMethod(["id" => "pm_3"]),
            ]),
        ]);

        $service = StripePaymentMethodService::make();
        $paymentMethods = $service->list(["customer" => "cus_123"]);

        expect($paymentMethods)
            ->toHaveCount(3)
            ->and($paymentMethods->first())->toBeInstanceOf(StripePaymentMethod::class)
            ->and($paymentMethods->first()->id())->toBe("pm_1")
            ->and($fake)->toHaveCalledStripeMethod("paymentMethods.all");
    });

    test("can use wildcard patterns for payment method methods", function (): void {
        Stripe::fake([
            "paymentMethods.*" => StripeFixtures::paymentMethod(["id" => "pm_wildcard"]),
        ]);

        $service = StripePaymentMethodService::make();
        $retrieved = $service->get("pm_any");
        $created = $service->create(StripePaymentMethod::make());

        expect($retrieved->id())->toBe("pm_wildcard")
            ->and($created->id())->toBe("pm_wildcard");
    });

    test("can create payment intent and attach payment method workflow", function (): void {
        Stripe::fake([
            "paymentMethods.create" => StripeFixtures::paymentMethod([
                "id" => "pm_new",
                "type" => "card",
            ]),
            "paymentMethods.attach" => StripeFixtures::paymentMethod([
                "id" => "pm_new",
                "customer" => "cus_123",
            ]),
        ]);

        $service = StripePaymentMethodService::make();

        $paymentMethod = $service->create(StripePaymentMethod::make()->withType(PaymentMethodType::Card));
        $attachedMethod = $service->attach($paymentMethod->id(), "cus_123");

        expect($attachedMethod->customer())->toBe("cus_123")
            ->and($attachedMethod->id())->toBe("pm_new");
    });
});
