<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Support\Building\Builders\AddressBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\BankAccountBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\CustomerBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\CustomUnitAmountBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\FinancialConnectionBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\PriceBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\ProductBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\RecurringBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\ShippingBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\SubscriptionBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\TierBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\TransactionRefreshBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\WebhookBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\StripeBuilder;

describe("StripeBuilder", function (): void {
    describe("Main Entity Builders", function (): void {
        test("can create customer builder", function (): void {
            $builder = new StripeBuilder;
            $customerBuilder = $builder->customer();

            expect($customerBuilder)->toBeInstanceOf(CustomerBuilder::class);
        });

        test("can create product builder", function (): void {
            $builder = new StripeBuilder;
            $productBuilder = $builder->product();

            expect($productBuilder)->toBeInstanceOf(ProductBuilder::class);
        });

        test("can create price builder", function (): void {
            $builder = new StripeBuilder;
            $priceBuilder = $builder->price();

            expect($priceBuilder)->toBeInstanceOf(PriceBuilder::class);
        });

        test("can create financial connection builder", function (): void {
            $builder = new StripeBuilder;
            $financialConnectionBuilder = $builder->financialConnection();

            expect($financialConnectionBuilder)->toBeInstanceOf(FinancialConnectionBuilder::class);
        });
    });

    describe("Support Object Builders", function (): void {
        test("can create address builder", function (): void {
            $builder = new StripeBuilder;
            $addressBuilder = $builder->address();

            expect($addressBuilder)->toBeInstanceOf(AddressBuilder::class);
        });

        test("can create shipping builder", function (): void {
            $builder = new StripeBuilder;
            $shippingBuilder = $builder->shipping();

            expect($shippingBuilder)->toBeInstanceOf(ShippingBuilder::class);
        });

        test("can create webhook builder", function (): void {
            $builder = new StripeBuilder;
            $webhookBuilder = $builder->webhook();

            expect($webhookBuilder)->toBeInstanceOf(WebhookBuilder::class);
        });
    });

    describe("Sub-Object Builders", function (): void {
        test("can create tier builder", function (): void {
            $builder = new StripeBuilder;
            $tierBuilder = $builder->tier();

            expect($tierBuilder)->toBeInstanceOf(TierBuilder::class);
        });

        test("can create custom unit amount builder", function (): void {
            $builder = new StripeBuilder;
            $customUnitAmountBuilder = $builder->customUnitAmount();

            expect($customUnitAmountBuilder)->toBeInstanceOf(CustomUnitAmountBuilder::class);
        });

        test("can create recurring builder", function (): void {
            $builder = new StripeBuilder;
            $recurringBuilder = $builder->recurring();

            expect($recurringBuilder)->toBeInstanceOf(RecurringBuilder::class);
        });

        test("can create bank account builder", function (): void {
            $builder = new StripeBuilder;
            $bankAccountBuilder = $builder->bankAccount();

            expect($bankAccountBuilder)->toBeInstanceOf(BankAccountBuilder::class);
        });

        test("can create transaction refresh builder", function (): void {
            $builder = new StripeBuilder;
            $transactionRefreshBuilder = $builder->transactionRefresh();

            expect($transactionRefreshBuilder)->toBeInstanceOf(TransactionRefreshBuilder::class);
        });
    });
});