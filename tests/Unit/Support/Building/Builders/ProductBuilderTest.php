<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeProduct;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\CustomUnitAmountBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\ProductBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\TierBuilder;

describe("ProductBuilder", function (): void {
    test("can build a product with basic parameters", function (): void {
        $builder = new ProductBuilder();
        $product = $builder->build(
            name: "Test Product",
            description: "A test product"
        );

        expect($product)
            ->toBeInstanceOf(StripeProduct::class)
            ->and($product->name)->toBe("Test Product")
            ->and($product->description)->toBe("A test product");
    });

    test("can build a product with all parameters", function (): void {
        $builder = new ProductBuilder();
        $created = CarbonImmutable::createFromTimestamp(1640995200);
        $updated = CarbonImmutable::createFromTimestamp(1640995300);
        $product = $builder->build(
            id: "prod_123",
            name: "Complete Product",
            description: "A complete test product",
            active: true,
            defaultPrice: "price_123",
            images: ["https://example.com/image.jpg"],
            metadata: ["key" => "value"],
            packageDimensions: [
                "height" => 10.0,
                "length" => 20.0,
                "weight" => 5.0,
                "width" => 15.0
            ],
            shippable: true,
            taxCode: "txcd_123",
            unitLabel: "piece",
            url: "https://example.com/product",
            created: $created,
            updated: $updated
        );

        expect($product)
            ->toBeInstanceOf(StripeProduct::class)
            ->and($product->id)->toBe("prod_123")
            ->and($product->name)->toBe("Complete Product")
            ->and($product->description)->toBe("A complete test product")
            ->and($product->active)->toBeTrue()
            ->and($product->defaultPrice)->toBe("price_123")
            ->and($product->images)->toBe(["https://example.com/image.jpg"])
            ->and($product->metadata)->toBe(["key" => "value"])
            ->and($product->shippable)->toBeTrue()
            ->and($product->taxCode)->toBe("txcd_123")
            ->and($product->unitLabel)->toBe("piece")
            ->and($product->url)->toBe("https://example.com/product")
            ->and($product->created)->toBe($created)
            ->and($product->updated)->toBe($updated);
    });

    describe("Nested Builders", function (): void {
        test("can access tier builder", function (): void {
            $builder = new ProductBuilder();
            $tierBuilder = $builder->tier();

            expect($tierBuilder)->toBeInstanceOf(TierBuilder::class);
        });

        test("can access custom unit amount builder", function (): void {
            $builder = new ProductBuilder();
            $customUnitAmountBuilder = $builder->customUnitAmount();

            expect($customUnitAmountBuilder)->toBeInstanceOf(CustomUnitAmountBuilder::class);
        });

    });
});