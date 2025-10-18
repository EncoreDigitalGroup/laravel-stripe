<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Support\Building\Builders\CustomUnitAmountBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\PriceBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\ProductBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\RecurringBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\TierBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\StripeBuilder;

describe("StripeBuilder", function (): void {
    describe("Main Entity Builders", function (): void {
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
    });
});