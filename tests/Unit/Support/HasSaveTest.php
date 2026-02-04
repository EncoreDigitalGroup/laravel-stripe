<?php

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeProduct;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;

describe("HasSave trait", function (): void {
    test("save calls create when id is null", function (): void {
        $fake = Stripe::fake([
            "products.create" => StripeFixtures::product([
                "id" => "prod_created123",
                "name" => "New Product",
            ]),
        ]);

        $product = StripeProduct::make(name: "New Product");
        $result = $product->save();

        expect($result)
            ->toBeInstanceOf(StripeProduct::class)
            ->and($result->id())->toBe("prod_created123")
            ->and($result->name())->toBe("New Product")
            ->and($fake)->toHaveCalledStripeMethod("products.create")
            ->and($fake)->toNotHaveCalledStripeMethod("products.update");
    });

    test("save calls update when id is present", function (): void {
        $fake = Stripe::fake([
            "products.update" => StripeFixtures::product([
                "id" => "prod_existing123",
                "name" => "Updated Product",
            ]),
        ]);

        $product = StripeProduct::make(
            id: "prod_existing123",
            name: "Updated Product"
        );
        $result = $product->save();

        expect($result)
            ->toBeInstanceOf(StripeProduct::class)
            ->and($result->id())->toBe("prod_existing123")
            ->and($result->name())->toBe("Updated Product")
            ->and($fake)->toHaveCalledStripeMethod("products.update")
            ->and($fake)->toNotHaveCalledStripeMethod("products.create");
    });

    test("save passes correct data to create", function (): void {
        $fake = Stripe::fake([
            "products.create" => function (array $params): array {
                return StripeFixtures::product([
                    "id" => "prod_new",
                    "name" => $params["name"] ?? "Test",
                    "description" => $params["description"] ?? null,
                ]);
            },
        ]);

        $product = StripeProduct::make(
            name: "Test Product",
            description: "Test Description"
        );
        $result = $product->save();

        expect($result->name())->toBe("Test Product")
            ->and($result->description())->toBe("Test Description");
    });

    test("save passes correct data to update", function (): void {
        $fake = Stripe::fake([
            "products.update" => function (array $params): array {
                return StripeFixtures::product([
                    "id" => "prod_update123",
                    "name" => $params["name"] ?? "Updated",
                ]);
            },
        ]);

        $product = StripeProduct::make(
            id: "prod_update123",
            name: "Updated Name"
        );
        $product->save();

        expect($fake)->toHaveCalledStripeMethod("products.update");
    });

    test("save returns self type", function (): void {
        Stripe::fake([
            "products.create" => StripeFixtures::product(),
        ]);

        $product = StripeProduct::make(name: "Test");
        $result = $product->save();

        expect($result)->toBeInstanceOf(StripeProduct::class);
    });

    test("save works with StripeCustomer for create", function (): void {
        $fake = Stripe::fake([
            "customers.create" => StripeFixtures::customer([
                "id" => "cus_new123",
                "email" => "new@example.com",
            ]),
        ]);

        $customer = StripeCustomer::make(email: "new@example.com");
        $result = $customer->save();

        expect($result)
            ->toBeInstanceOf(StripeCustomer::class)
            ->and($result->id())->toBe("cus_new123")
            ->and($result->email())->toBe("new@example.com")
            ->and($fake)->toHaveCalledStripeMethod("customers.create");
    });

    test("save works with StripeCustomer for update", function (): void {
        $fake = Stripe::fake([
            "customers.update" => StripeFixtures::customer([
                "id" => "cus_existing123",
                "email" => "updated@example.com",
            ]),
        ]);

        $customer = StripeCustomer::make(
            id: "cus_existing123",
            email: "updated@example.com"
        );
        $result = $customer->save();

        expect($result)
            ->toBeInstanceOf(StripeCustomer::class)
            ->and($result->id())->toBe("cus_existing123")
            ->and($result->email())->toBe("updated@example.com")
            ->and($fake)->toHaveCalledStripeMethod("customers.update");
    });
});