<?php



use EncoreDigitalGroup\StdLib\Exceptions\NotImplementedException;
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeProduct;
use EncoreDigitalGroup\Stripe\Services\StripeCustomerService;
use EncoreDigitalGroup\Stripe\Services\StripeProductService;
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Traits\HasService;

describe("HasService trait", function (): void {
    test("throws NotImplementedException when service method is not overridden", function (): void {
        $instance = new class()
        {
            use HasService;
        };

        expect(fn (): mixed => $instance->service())
            ->toThrow(NotImplementedException::class);
    });

    test("StripeProduct implements service method returning StripeProductService", function (): void {
        Stripe::fake();

        $product = StripeProduct::make(name: "Test Product");

        $service = $product->service();

        expect($service)
            ->toBeInstanceOf(StripeProductService::class);
    });

    test("StripeCustomer implements service method returning StripeCustomerService", function (): void {
        Stripe::fake();

        $customer = StripeCustomer::make(email: "test@example.com");

        $service = $customer->service();

        expect($service)
            ->toBeInstanceOf(StripeCustomerService::class);
    });
});