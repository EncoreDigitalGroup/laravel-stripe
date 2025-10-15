<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeShipping;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\AddressBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\ShippingBuilder;

describe("ShippingBuilder", function (): void {
    test("can build a basic shipping", function (): void {
        $address = StripeAddress::make(line1: "123 Main St", city: "Boston", country: "US");
        $builder = new ShippingBuilder;
        $shipping = $builder->build(
            address: $address,
            name: "John Doe"
        );

        expect($shipping)
            ->toBeInstanceOf(StripeShipping::class)
            ->and($shipping->address)->toBe($address)
            ->and($shipping->name)->toBe("John Doe")
            ->and($shipping->phone)->toBeNull();
    });

    test("can build shipping with all parameters", function (): void {
        $address = StripeAddress::make(
            line1: "456 Oak Ave",
            line2: "Apt 2B",
            city: "Cambridge",
            state: "MA",
            postalCode: "02138",
            country: "US"
        );
        $builder = new ShippingBuilder;
        $shipping = $builder->build(
            address: $address,
            name: "Jane Smith",
            phone: "+1234567890"
        );

        expect($shipping)
            ->toBeInstanceOf(StripeShipping::class)
            ->and($shipping->address)->toBe($address)
            ->and($shipping->name)->toBe("Jane Smith")
            ->and($shipping->phone)->toBe("+1234567890");
    });

    describe("Nested Builders", function (): void {
        test("can access address builder", function (): void {
            $builder = new ShippingBuilder;
            $addressBuilder = $builder->address();

            expect($addressBuilder)->toBeInstanceOf(AddressBuilder::class);
        });
    });
});