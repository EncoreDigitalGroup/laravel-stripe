<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Objects\Product\StripePrice;
use EncoreDigitalGroup\Stripe\Services\StripePriceService;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

test("can create a price", function (): void {
    $fake = Stripe::fake([
        StripeMethod::PricesCreate->value => StripeFixtures::price([
            "id" => "price_test123",
            "unit_amount" => 2000,
            "currency" => "usd",
        ]),
    ]);

    $price = StripePrice::make(
        product: "prod_test",
        unitAmount: 2000,
        currency: "usd"
    );

    $service = StripePriceService::make();
    $result = $service->create($price);

    expect($result)
        ->toBeInstanceOf(StripePrice::class)
        ->and($result->id())->toBe("price_test123")
        ->and($result->unitAmount())->toBe(2000)
        ->and($result->currency())->toBe("usd")
        ->and($fake)->toHaveCalledStripeMethod(StripeMethod::PricesCreate);
});

test("can retrieve a price", function (): void {
    $fake = Stripe::fake([
        "prices.retrieve" => StripeFixtures::price([
            "id" => "price_existing",
            "unit_amount" => 1500,
        ]),
    ]);

    $service = StripePriceService::make();
    $price = $service->get("price_existing");

    expect($price)
        ->toBeInstanceOf(StripePrice::class)
        ->and($price->id())->toBe("price_existing")
        ->and($price->unitAmount())->toBe(1500)
        ->and($fake)->toHaveCalledStripeMethod("prices.retrieve");
});

test("can update a price with allowed fields", function (): void {
    $fake = Stripe::fake([
        "prices.update" => StripeFixtures::price([
            "id" => "price_123",
            "nickname" => "Updated Nickname",
            "active" => true,
        ]),
    ]);

    $price = StripePrice::make(
        nickname: "Updated Nickname",
        active: true
    );

    $service = StripePriceService::make();
    $result = $service->update("price_123", $price);

    expect($result)
        ->toBeInstanceOf(StripePrice::class)
        ->and($result->nickname())->toBe("Updated Nickname")
        ->and($fake)->toHaveCalledStripeMethod("prices.update");
});

test("can archive a price", function (): void {
    $fake = Stripe::fake([
        "prices.update" => StripeFixtures::price([
            "id" => "price_123",
            "active" => false,
        ]),
    ]);

    $service = StripePriceService::make();
    $result = $service->archive("price_123");

    expect($result)
        ->toBeInstanceOf(StripePrice::class)
        ->and($result->active())->toBeFalse()
        ->and($fake)->toHaveCalledStripeMethod("prices.update");
});

test("can reactivate a price", function (): void {
    $fake = Stripe::fake([
        "prices.update" => StripeFixtures::price([
            "id" => "price_123",
            "active" => true,
        ]),
    ]);

    $service = StripePriceService::make();
    $result = $service->reactivate("price_123");

    expect($result)
        ->toBeInstanceOf(StripePrice::class)
        ->and($result->active())->toBeTrue()
        ->and($fake)->toHaveCalledStripeMethod("prices.update");
});

test("can list prices", function (): void {
    $fake = Stripe::fake([
        "prices.all" => StripeFixtures::priceList([
            StripeFixtures::price(["id" => "price_1", "unit_amount" => 1000]),
            StripeFixtures::price(["id" => "price_2", "unit_amount" => 2000]),
            StripeFixtures::price(["id" => "price_3", "unit_amount" => 3000]),
        ]),
    ]);

    $service = StripePriceService::make();
    $prices = $service->list(["limit" => 10]);

    expect($prices)
        ->toHaveCount(3)
        ->and($prices->first())->toBeInstanceOf(StripePrice::class)
        ->and($prices->first()->id())->toBe("price_1")
        ->and($fake)->toHaveCalledStripeMethod("prices.all");
});

test("can list prices by product", function (): void {
    $fake = Stripe::fake([
        "prices.all" => StripeFixtures::priceList([
            StripeFixtures::price(["id" => "price_1", "product" => "prod_123"]),
        ]),
    ]);

    $service = StripePriceService::make();
    $prices = $service->listByProduct("prod_123");

    expect($prices)->toHaveCount(1);

    $params = $fake->getCall("prices.all");
    expect($params)->toHaveKey("product")
        ->and($params["product"])->toBe("prod_123");
});

test("can search prices", function (): void {
    $fake = Stripe::fake([
        "prices.search" => StripeFixtures::priceList([
            StripeFixtures::price(["id" => "price_1", "nickname" => "Premium Plan"]),
        ]),
    ]);

    $service = StripePriceService::make();
    $prices = $service->search('nickname:"Premium Plan"');

    expect($prices)
        ->toHaveCount(1)
        ->and($prices->first())->toBeInstanceOf(StripePrice::class)
        ->and($prices->first()->nickname())->toBe("Premium Plan")
        ->and($fake)->toHaveCalledStripeMethod("prices.search");
});

test("can get price by lookup key", function (): void {
    $fake = Stripe::fake([
        "prices.all" => StripeFixtures::priceList([
            StripeFixtures::price(["id" => "price_1", "lookup_key" => "premium_monthly"]),
        ]),
    ]);

    $service = StripePriceService::make();
    $price = $service->getByLookupKey("premium_monthly");

    expect($price)
        ->toBeInstanceOf(StripePrice::class)
        ->and($price->lookupKey())->toBe("premium_monthly");

    $params = $fake->getCall("prices.all");
    expect($params)->toHaveKey("lookup_keys")
        ->and($params["lookup_keys"])->toBe(["premium_monthly"]);
});

test("get price by lookup key returns null when not found", function (): void {
    $fake = Stripe::fake([
        "prices.all" => StripeFixtures::priceList([]),
    ]);

    $service = StripePriceService::make();
    $price = $service->getByLookupKey("nonexistent");

    expect($price)->toBeNull();
});

test("create removes id and created from payload", function (): void {
    $fake = Stripe::fake([
        "prices.create" => StripeFixtures::price(["id" => "price_new"]),
    ]);

    $price = StripePrice::make(
        id: "should_be_removed",
        product: "prod_test",
        unitAmount: 1000,
        currency: "usd",
        created: CarbonImmutable::createFromTimestamp(1234567890)
    );

    $service = StripePriceService::make();
    $service->create($price);

    $params = $fake->getCall("prices.create");

    expect($params)->not->toHaveKey("id")
        ->and($params)->not->toHaveKey("created")
        ->and($params)->toHaveKey("product");
});

test("update removes immutable fields from payload", function (): void {
    $fake = Stripe::fake([
        "prices.update" => StripeFixtures::price(["id" => "price_123"]),
    ]);

    $price = StripePrice::make(
        id: "should_be_removed",
        product: "should_be_removed",
        unitAmount: 9999,
        currency: "usd",
        nickname: "Updated Nickname",
        active: true
    );

    $service = StripePriceService::make();
    $service->update("price_123", $price);

    $params = $fake->getCall("prices.update");

    // These fields should be removed
    expect($params)->not->toHaveKey("id")
        ->and($params)->not->toHaveKey("product")
        ->and($params)->not->toHaveKey("unit_amount")
        ->and($params)->not->toHaveKey("currency")
        // These fields should remain
        ->and($params)->toHaveKey("nickname")
        ->and($params)->toHaveKey("active");
});
