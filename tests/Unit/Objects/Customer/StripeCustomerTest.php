<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\StdLib\Exceptions\NullExceptions\ClassPropertyNullException;
use EncoreDigitalGroup\StdLib\Exceptions\NullExceptions\VariableNullException;
use EncoreDigitalGroup\Stripe\Enums\PaymentMethodType;
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\Payment\StripePaymentMethod;
use EncoreDigitalGroup\Stripe\Objects\Payment\StripeSetupIntent;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

describe("addPaymentMethod", function (): void {
    test("creates and attaches payment method to customer", function (): void {
        // Arrange
        $fake = Stripe::fake([
            StripeMethod::PaymentMethodsCreate->value => StripeFixtures::paymentMethod([
                "id" => "pm_test123",
                "type" => "card",
            ]),
            StripeMethod::PaymentMethodsAttach->value => StripeFixtures::paymentMethod([
                "id" => "pm_test123",
                "type" => "card",
                "customer" => "cus_test123",
            ]),
        ]);

        $customer = StripeCustomer::make()->withId("cus_test123");
        $paymentMethod = StripePaymentMethod::make()->withType(PaymentMethodType::Card);

        // Act
        $result = $customer->addPaymentMethod($paymentMethod);

        // Assert
        expect($result)
            ->toBeInstanceOf(StripeCustomer::class)
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::PaymentMethodsCreate)
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::PaymentMethodsAttach);
    });

    test("throws exception when customer has no id", function (): void {
        // Arrange
        Stripe::fake([
            StripeMethod::PaymentMethodsCreate->value => StripeFixtures::paymentMethod([
                "id" => "pm_test123",
            ]),
        ]);

        $customer = StripeCustomer::make();
        $paymentMethod = StripePaymentMethod::make()->withType(PaymentMethodType::Card);

        // Act & Assert
        expect(fn(): StripeCustomer => $customer->addPaymentMethod($paymentMethod))
            ->toThrow(ClassPropertyNullException::class);
    });

    test("throws exception when created payment method has no id", function (): void {
        // Arrange
        Stripe::fake([
            StripeMethod::PaymentMethodsCreate->value => StripeFixtures::paymentMethod([
                "id" => null,
            ]),
        ]);

        $customer = StripeCustomer::make()->withId("cus_test123");
        $paymentMethod = StripePaymentMethod::make()->withType(PaymentMethodType::Card);

        // Act & Assert
        expect(fn(): StripeCustomer => $customer->addPaymentMethod($paymentMethod))
            ->toThrow(VariableNullException::class);
    });

    test("refreshes payment methods collection after adding", function (): void {
        // Arrange
        $fake = Stripe::fake([
            StripeMethod::PaymentMethodsAll->value => StripeFixtures::paymentMethodList([
                StripeFixtures::paymentMethod(["id" => "pm_existing"]),
            ]),
        ]);

        $customer = StripeCustomer::make()->withId("cus_test123");

        // Load payment methods to populate the collection
        $initialPaymentMethods = $customer->paymentMethods();
        expect($initialPaymentMethods)->toHaveCount(1);

        // Reset fake to track new calls
        $fake = Stripe::fake([
            StripeMethod::PaymentMethodsCreate->value => StripeFixtures::paymentMethod([
                "id" => "pm_new",
                "type" => "card",
            ]),
            StripeMethod::PaymentMethodsAttach->value => StripeFixtures::paymentMethod([
                "id" => "pm_new",
                "type" => "card",
                "customer" => "cus_test123",
            ]),
            StripeMethod::PaymentMethodsAll->value => StripeFixtures::paymentMethodList([
                StripeFixtures::paymentMethod(["id" => "pm_existing"]),
                StripeFixtures::paymentMethod(["id" => "pm_new"]),
            ]),
        ]);

        $paymentMethod = StripePaymentMethod::make()->withType(PaymentMethodType::Card);

        // Act
        $customer->addPaymentMethod($paymentMethod);

        // Assert
        expect($fake)->toHaveCalledStripeMethod(StripeMethod::PaymentMethodsAll);
    });

    test("does not refresh payment methods collection when not previously loaded", function (): void {
        // Arrange
        $fake = Stripe::fake([
            StripeMethod::PaymentMethodsCreate->value => StripeFixtures::paymentMethod([
                "id" => "pm_new",
                "type" => "card",
            ]),
            StripeMethod::PaymentMethodsAttach->value => StripeFixtures::paymentMethod([
                "id" => "pm_new",
                "type" => "card",
                "customer" => "cus_test123",
            ]),
        ]);

        $customer = StripeCustomer::make()->withId("cus_test123");
        $paymentMethod = StripePaymentMethod::make()->withType(PaymentMethodType::Card);

        // Act
        $customer->addPaymentMethod($paymentMethod);

        // Assert
        expect($fake)->toNotHaveCalledStripeMethod(StripeMethod::PaymentMethodsAll);
    });

    test("passes correct payment method type to create", function (): void {
        // Arrange
        $fake = Stripe::fake([
            StripeMethod::PaymentMethodsCreate->value => StripeFixtures::paymentMethod([
                "id" => "pm_test123",
                "type" => "us_bank_account",
            ]),
            StripeMethod::PaymentMethodsAttach->value => StripeFixtures::paymentMethod([
                "id" => "pm_test123",
                "customer" => "cus_test123",
            ]),
        ]);

        $customer = StripeCustomer::make()->withId("cus_test123");
        $paymentMethod = StripePaymentMethod::make()->withType(PaymentMethodType::UsBankAccount);

        // Act
        $customer->addPaymentMethod($paymentMethod);

        // Assert
        expect($fake)->toHaveCalledStripeMethod(
            StripeMethod::PaymentMethodsCreate,
            ["type" => "us_bank_account"]
        );
    });

    test("passes correct customer id to attach", function (): void {
        // Arrange
        $fake = Stripe::fake([
            StripeMethod::PaymentMethodsCreate->value => StripeFixtures::paymentMethod([
                "id" => "pm_test123",
            ]),
            StripeMethod::PaymentMethodsAttach->value => StripeFixtures::paymentMethod([
                "id" => "pm_test123",
                "customer" => "cus_abc123",
            ]),
        ]);

        $customer = StripeCustomer::make()->withId("cus_abc123");
        $paymentMethod = StripePaymentMethod::make()->withType(PaymentMethodType::Card);

        // Act
        $customer->addPaymentMethod($paymentMethod);

        // Assert
        expect($fake)->toHaveCalledStripeMethod(
            StripeMethod::PaymentMethodsAttach,
            ["customer" => "cus_abc123"]
        );
    });
});

describe("createSetupIntent", function (): void {
    test("creates a setup intent with customer id", function (): void {
        $customer = StripeCustomer::make()->withId("cus_test123");

        $setupIntent = $customer->createSetupIntent();

        expect($setupIntent)
            ->toBeInstanceOf(StripeSetupIntent::class)
            ->and($setupIntent->customer())->toBe("cus_test123");
    });

    test("throws exception when customer has no id", function (): void {
        $customer = StripeCustomer::make();

        expect(fn(): StripeSetupIntent => $customer->createSetupIntent())
            ->toThrow(ClassPropertyNullException::class);
    });

    test("creates setup intent that can be customized with description", function (): void {
        $customer = StripeCustomer::make()->withId("cus_test123");

        $setupIntent = $customer->createSetupIntent()->withDescription("Save card for future payments");

        expect($setupIntent)
            ->toBeInstanceOf(StripeSetupIntent::class)
            ->and($setupIntent->customer())->toBe("cus_test123")
            ->and($setupIntent->description())->toBe("Save card for future payments");
    });

    test("creates setup intent that can be customized with metadata", function (): void {
        $customer = StripeCustomer::make()->withId("cus_test123");
        $metadata = ["user_id" => "123", "plan" => "premium"];

        $setupIntent = $customer->createSetupIntent()->withMetadata($metadata);

        expect($setupIntent)
            ->toBeInstanceOf(StripeSetupIntent::class)
            ->and($setupIntent->customer())->toBe("cus_test123")
            ->and($setupIntent->metadata())->toBe($metadata);
    });

    test("creates setup intent that can be customized with both description and metadata", function (): void {
        $customer = StripeCustomer::make()->withId("cus_test123");
        $metadata = ["subscription_id" => "sub_456"];

        $setupIntent = $customer->createSetupIntent()
            ->withDescription("Setup for subscription")
            ->withMetadata($metadata);

        expect($setupIntent)
            ->toBeInstanceOf(StripeSetupIntent::class)
            ->and($setupIntent->customer())->toBe("cus_test123")
            ->and($setupIntent->description())->toBe("Setup for subscription")
            ->and($setupIntent->metadata())->toBe($metadata);
    });

    test("returns setup intent that can be saved", function (): void {
        $fake = Stripe::fake([
            StripeMethod::SetupIntentsCreate->value => StripeFixtures::setupIntent([
                "id" => "seti_test123",
                "customer" => "cus_test123",
                "client_secret" => "seti_test123_secret_abc",
            ]),
        ]);

        $customer = StripeCustomer::make()->withId("cus_test123");

        $setupIntent = $customer->createSetupIntent();
        $savedSetupIntent = $setupIntent->save();

        expect($savedSetupIntent)
            ->toBeInstanceOf(StripeSetupIntent::class)
            ->and($savedSetupIntent->id())->toBe("seti_test123")
            ->and($savedSetupIntent->customer())->toBe("cus_test123")
            ->and($savedSetupIntent->clientSecret())->toBe("seti_test123_secret_abc")
            ->and($fake)->toHaveCalledStripeMethod(StripeMethod::SetupIntentsCreate);
    });

    test("creates setup intent with minimal configuration", function (): void {
        $customer = StripeCustomer::make()->withId("cus_test123");

        $setupIntent = $customer->createSetupIntent();

        expect($setupIntent->customer())->toBe("cus_test123")
            ->and($setupIntent->description())->toBeNull()
            ->and($setupIntent->metadata())->toBeNull();
    });
});
