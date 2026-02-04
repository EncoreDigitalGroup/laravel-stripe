<?php

use EncoreDigitalGroup\Stripe\Support\Config\Authentication;
use EncoreDigitalGroup\Stripe\Support\Config\StripeConfig;

describe("StripeConfig", function (): void {
    test("can create StripeConfig using make method", function (): void {
        $config = StripeConfig::make();

        expect($config)
            ->toBeInstanceOf(StripeConfig::class)
            ->and($config->authentication)->toBeInstanceOf(Authentication::class);
    });

    test("make method returns singleton instance", function (): void {
        $config1 = StripeConfig::make();
        $config2 = StripeConfig::make();

        expect($config1)->toBe($config2);
    });

    test("can instantiate directly with constructor", function (): void {
        $config = new StripeConfig;

        expect($config)
            ->toBeInstanceOf(StripeConfig::class)
            ->and($config->authentication)->toBeInstanceOf(Authentication::class);
    });
});