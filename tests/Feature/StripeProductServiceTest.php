<?php

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeProduct;
use EncoreDigitalGroup\Stripe\Services\StripeProductService;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

test("can create a product", function (): void {
    $fake = Stripe::fake([
        StripeMethod::ProductsCreate->value => StripeFixtures::product([
            "id" => "prod_test123",
            "name" => "Test Product",
            "description" => "A test product",
        ]),
    ]);

    $product = StripeProduct::make(
        name: "Test Product",
        description: "A test product"
    );

    $service = StripeProductService::make();
    $result = $service->create($product);

    expect($result)
        ->toBeInstanceOf(StripeProduct::class)
        ->and($result->id())->toBe("prod_test123")
        ->and($result->name())->toBe("Test Product")
        ->and($result->description())->toBe("A test product")
        ->and($fake)->toHaveCalledStripeMethod(StripeMethod::ProductsCreate);

});

test("can retrieve a product", function (): void {
    $fake = Stripe::fake([
        "products.retrieve" => StripeFixtures::product([
            "id" => "prod_existing",
            "name" => "Existing Product",
        ]),
    ]);

    $service = StripeProductService::make();
    $product = $service->get("prod_existing");

    expect($product)
        ->toBeInstanceOf(StripeProduct::class)
        ->and($product->id())->toBe("prod_existing")
        ->and($product->name())->toBe("Existing Product")
        ->and($fake)->toHaveCalledStripeMethod("products.retrieve");

});

test("can update a product", function (): void {
    $fake = Stripe::fake([
        "products.update" => StripeFixtures::product([
            "id" => "prod_123",
            "name" => "Updated Product",
            "description" => "Updated description",
        ]),
    ]);

    $product = StripeProduct::make(
        name: "Updated Product",
        description: "Updated description"
    );

    $service = StripeProductService::make();
    $result = $service->update("prod_123", $product);

    expect($result)
        ->toBeInstanceOf(StripeProduct::class)
        ->and($result->name())->toBe("Updated Product")
        ->and($result->description())->toBe("Updated description")
        ->and($fake)->toHaveCalledStripeMethod("products.update");

});

test("can delete a product", function (): void {
    $fake = Stripe::fake([
        "products.delete" => StripeFixtures::deleted("prod_123", "product"),
    ]);

    $service = StripeProductService::make();
    $result = $service->delete("prod_123");

    expect($result)->toBeTrue()
        ->and($fake)->toHaveCalledStripeMethod("products.delete");
});

test("can archive a product", function (): void {
    $fake = Stripe::fake([
        "products.update" => StripeFixtures::product([
            "id" => "prod_123",
            "active" => false,
        ]),
    ]);

    $service = StripeProductService::make();
    $result = $service->archive("prod_123");

    expect($result)
        ->toBeInstanceOf(StripeProduct::class)
        ->and($result->active())->toBeFalse()
        ->and($fake)->toHaveCalledStripeMethod("products.update");
});

test("can reactivate a product", function (): void {
    $fake = Stripe::fake([
        "products.update" => StripeFixtures::product([
            "id" => "prod_123",
            "active" => true,
        ]),
    ]);

    $service = StripeProductService::make();
    $result = $service->reactivate("prod_123");

    expect($result)
        ->toBeInstanceOf(StripeProduct::class)
        ->and($result->active())->toBeTrue()
        ->and($fake)->toHaveCalledStripeMethod("products.update");
});

test("can list products", function (): void {
    $fake = Stripe::fake([
        "products.all" => StripeFixtures::productList([
            StripeFixtures::product(["id" => "prod_1", "name" => "Product 1"]),
            StripeFixtures::product(["id" => "prod_2", "name" => "Product 2"]),
            StripeFixtures::product(["id" => "prod_3", "name" => "Product 3"]),
        ]),
    ]);

    $service = StripeProductService::make();
    $products = $service->list(["limit" => 10]);

    expect($products)
        ->toHaveCount(3)
        ->and($products->first())->toBeInstanceOf(StripeProduct::class)
        ->and($products->first()->id())->toBe("prod_1")
        ->and($fake)->toHaveCalledStripeMethod("products.all");
});

test("can search products", function (): void {
    $fake = Stripe::fake([
        "products.search" => StripeFixtures::productList([
            StripeFixtures::product(["id" => "prod_1", "name" => "Searchable Product"]),
        ]),
    ]);

    $service = StripeProductService::make();
    $products = $service->search("name:\"Searchable Product\"");

    expect($products)
        ->toHaveCount(1)
        ->and($products->first())->toBeInstanceOf(StripeProduct::class)
        ->and($products->first()->name())->toBe("Searchable Product")
        ->and($fake)->toHaveCalledStripeMethod("products.search");
});

test("create removes id and timestamps from payload", function (): void {
    $fake = Stripe::fake([
        "products.create" => StripeFixtures::product(["id" => "prod_new"]),
    ]);

    $product = StripeProduct::make(
        id: "should_be_removed",
        name: "Test Product",
        created: CarbonImmutable::createFromTimestamp(1234567890),
        updated: CarbonImmutable::createFromTimestamp(1234567890)
    );

    $service = StripeProductService::make();
    $service->create($product);

    $params = $fake->getCall("products.create");

    expect($params)->not->toHaveKey("id")
        ->and($params)->not->toHaveKey("created")
        ->and($params)->not->toHaveKey("updated")
        ->and($params)->toHaveKey("name");
});

test("update removes id and timestamps from payload", function (): void {
    $fake = Stripe::fake([
        "products.update" => StripeFixtures::product(["id" => "prod_123"]),
    ]);

    $product = StripeProduct::make(
        id: "should_be_removed",
        name: "Updated Product",
        created: CarbonImmutable::createFromTimestamp(1234567890),
        updated: CarbonImmutable::createFromTimestamp(1234567890)
    );

    $service = StripeProductService::make();
    $service->update("prod_123", $product);

    $params = $fake->getCall("products.update");

    expect($params)->not->toHaveKey("id")
        ->and($params)->not->toHaveKey("created")
        ->and($params)->not->toHaveKey("updated")
        ->and($params)->toHaveKey("name");
});
