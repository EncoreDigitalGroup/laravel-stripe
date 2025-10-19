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
