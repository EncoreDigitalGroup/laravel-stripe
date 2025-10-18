<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Product\StripeProduct;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use Stripe\Util\Util;

test("can create StripeProduct using make method", function (): void {
    $product = StripeProduct::make(
        name: "Test Product",
        description: "Test Description",
        active: true
    );

    expect($product)
        ->toBeInstanceOf(StripeProduct::class)
        ->and($product->name())->toBe("Test Product")
        ->and($product->description())->toBe("Test Description")
        ->and($product->active())->toBeTrue();
});

test("can create StripeProduct from Stripe object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "prod_123",
        "object" => "product",
        "name" => "Test Product",
        "description" => "Test Description",
        "active" => true,
        "images" => ["image1.jpg", "image2.jpg"],
        "metadata" => ["key" => "value"],
        "default_price" => "price_123",
        "tax_code" => "txcd_123",
        "unit_label" => "unit",
        "url" => "https://example.com",
        "shippable" => true,
        "package_dimensions" => [
            "height" => 10,
            "length" => 20,
            "weight" => 30,
            "width" => 15,
        ],
        "created" => 1234567890,
        "updated" => 1234567891,
    ], []);

    $product = StripeProduct::fromStripeObject($stripeObject);

    expect($product)
        ->toBeInstanceOf(StripeProduct::class)
        ->and($product->id())->toBe("prod_123")
        ->and($product->name())->toBe("Test Product")
        ->and($product->description())->toBe("Test Description")
        ->and($product->active())->toBeTrue()
        ->and($product->images())->toBe(["image1.jpg", "image2.jpg"])
        ->and($product->metadata())->toBe(["key" => "value"])
        ->and($product->defaultPrice())->toBe("price_123")
        ->and($product->taxCode())->toBe("txcd_123")
        ->and($product->unitLabel())->toBe("unit")
        ->and($product->url())->toBe("https://example.com")
        ->and($product->shippable())->toBeTrue()
        ->and($product->packageDimensions())->toBeArray()
        ->and($product->created())->toBeInstanceOf(\Carbon\CarbonImmutable::class)
        ->and($product->created()->timestamp)->toBe(1234567890)
        ->and($product->updated())->toBeInstanceOf(\Carbon\CarbonImmutable::class)
        ->and($product->updated()->timestamp)->toBe(1234567891);
});

test("fromStripeObject handles nested default_price object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "prod_123",
        "object" => "product",
        "name" => "Test Product",
        "default_price" => [
            "id" => "price_123",
            "object" => "price",
        ],
        "metadata" => [],
    ], []);

    $product = StripeProduct::fromStripeObject($stripeObject);

    expect($product->defaultPrice())->toBe("price_123");
});

test("fromStripeObject handles nested tax_code object", function (): void {
    $stripeObject = Util::convertToStripeObject([
        "id" => "prod_123",
        "object" => "product",
        "name" => "Test Product",
        "tax_code" => [
            "id" => "txcd_123",
            "object" => "tax_code",
        ],
        "metadata" => [],
    ], []);

    $product = StripeProduct::fromStripeObject($stripeObject);

    expect($product->taxCode())->toBe("txcd_123");
});

test("toArray returns correct structure", function (): void {
    $product = StripeProduct::make(
        id: "prod_123",
        name: "Test Product",
        description: "Test Description",
        active: true,
        images: ["image1.jpg"],
        metadata: ["key" => "value"],
        defaultPrice: "price_123",
        taxCode: "txcd_123",
        unitLabel: "unit",
        url: "https://example.com",
        shippable: true,
        packageDimensions: ["height" => 10, "length" => 20, "weight" => 30, "width" => 15]
    );

    $array = $product->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("id")
        ->and($array)->toHaveKey("name")
        ->and($array)->toHaveKey("description")
        ->and($array)->toHaveKey("active")
        ->and($array)->toHaveKey("images")
        ->and($array)->toHaveKey("metadata")
        ->and($array)->toHaveKey("default_price")
        ->and($array)->toHaveKey("tax_code")
        ->and($array)->toHaveKey("unit_label")
        ->and($array)->toHaveKey("url")
        ->and($array)->toHaveKey("shippable")
        ->and($array)->toHaveKey("package_dimensions");
});

test("toArray filters null values", function (): void {
    $product = StripeProduct::make(
        name: "Test Product"
        // All other fields are null
    );

    $array = $product->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey("name")
        ->and($array)->not->toHaveKey("id")
        ->and($array)->not->toHaveKey("description")
        ->and($array)->not->toHaveKey("active");
});

test("can round trip from Stripe object to array", function (): void {
    $originalData = StripeFixtures::product([
        "id" => "prod_123",
        "name" => "Test Product",
        "description" => "Test Description",
    ]);

    $stripeObject = Util::convertToStripeObject($originalData, []);
    $product = StripeProduct::fromStripeObject($stripeObject);
    $array = $product->toArray();

    expect($array)->toHaveKey("id")
        ->and($array["id"])->toBe("prod_123")
        ->and($array)->toHaveKey("name")
        ->and($array["name"])->toBe("Test Product")
        ->and($array)->toHaveKey("description")
        ->and($array["description"])->toBe("Test Description");
});
