<?php



use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeProduct;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;

describe("HasGet trait", function (): void {
    test("StripeProduct get method retrieves product by id", function (): void {
        $fake = Stripe::fake([
            "products.retrieve" => StripeFixtures::product([
                "id" => "prod_test123",
                "name" => "Retrieved Product",
            ]),
        ]);

        $product = StripeProduct::make();
        $result = $product->get("prod_test123");

        expect($result)
            ->toBeInstanceOf(StripeProduct::class)
            ->and($result->id())->toBe("prod_test123")
            ->and($result->name())->toBe("Retrieved Product")
            ->and($fake)->toHaveCalledStripeMethod("products.retrieve");
    });

    test("StripeCustomer get method retrieves customer by id", function (): void {
        $fake = Stripe::fake([
            "customers.retrieve" => StripeFixtures::customer([
                "id" => "cus_test123",
                "email" => "retrieved@example.com",
            ]),
        ]);

        $customer = StripeCustomer::make();
        $result = $customer->get("cus_test123");

        expect($result)
            ->toBeInstanceOf(StripeCustomer::class)
            ->and($result->id())->toBe("cus_test123")
            ->and($result->email())->toBe("retrieved@example.com")
            ->and($fake)->toHaveCalledStripeMethod("customers.retrieve");
    });

    test("get method calls service get with correct id", function (): void {
        $fake = Stripe::fake([
            "products.retrieve" => StripeFixtures::product([
                "id" => "prod_specific_id",
            ]),
        ]);

        $product = StripeProduct::make();
        $product->get("prod_specific_id");

        expect($fake)->toHaveCalledStripeMethod("products.retrieve");
    });

    test("get method returns self type", function (): void {
        Stripe::fake([
            "products.retrieve" => StripeFixtures::product(),
        ]);

        $product = StripeProduct::make();
        $result = $product->get("prod_123");

        expect($result)->toBeInstanceOf(StripeProduct::class);
    });
});