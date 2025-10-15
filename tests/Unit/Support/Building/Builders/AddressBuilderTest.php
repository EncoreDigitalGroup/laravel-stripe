<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\AddressBuilder;

describe("AddressBuilder", function (): void {
    test("can build a basic address", function (): void {
        $builder = new AddressBuilder;
        $address = $builder->build(
            line1: "123 Main St"
        );

        expect($address)
            ->toBeInstanceOf(StripeAddress::class)
            ->and($address->line1)->toBe("123 Main St");
    });

    test("can build address with all parameters", function (): void {
        $builder = new AddressBuilder;
        $address = $builder->build(
            line1: "123 Main St",
            line2: "Apt 4B",
            city: "Boston",
            state: "MA",
            postalCode: "02101",
            country: "US"
        );

        expect($address)
            ->toBeInstanceOf(StripeAddress::class)
            ->and($address->line1)->toBe("123 Main St")
            ->and($address->line2)->toBe("Apt 4B")
            ->and($address->city)->toBe("Boston")
            ->and($address->state)->toBe("MA")
            ->and($address->postalCode)->toBe("02101")
            ->and($address->country)->toBe("US");
    });

    test("can build minimal address", function (): void {
        $builder = new AddressBuilder;
        $address = $builder->build(
            line1: "456 Oak Ave",
            city: "Cambridge",
            country: "US"
        );

        expect($address)
            ->toBeInstanceOf(StripeAddress::class)
            ->and($address->line1)->toBe("456 Oak Ave")
            ->and($address->line2)->toBeNull()
            ->and($address->city)->toBe("Cambridge")
            ->and($address->state)->toBeNull()
            ->and($address->postalCode)->toBeNull()
            ->and($address->country)->toBe("US");
    });
});