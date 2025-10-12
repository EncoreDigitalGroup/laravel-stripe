<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe;

use EncoreDigitalGroup\Common\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Common\Stripe\Objects\FinancialConnections\StripeFinancialConnection;
use EncoreDigitalGroup\Common\Stripe\Objects\Support\StripeWebhook;
use EncoreDigitalGroup\Common\Stripe\Services\StripeCustomerService;
use EncoreDigitalGroup\Common\Stripe\Support\HasStripe;

class Stripe
{
    use HasStripe;

    public static function customer(mixed ...$params): StripeCustomer
    {
        return StripeCustomer::make(...$params);
    }

    public static function financialConnections(mixed ...$params): StripeFinancialConnection
    {
        return StripeFinancialConnection::make(...$params);
    }

    public static function webhook(mixed ...$params): StripeWebhook
    {
        return StripeWebhook::make(...$params);
    }

    public static function customers(): StripeCustomerService
    {
        return StripeCustomerService::make();
    }

    /**
     * Create a fake Stripe client for testing
     *
     * This method is only available when the FakeStripeClient class exists (in test environment).
     *
     * @param array $fakes Array of method => response mappings
     * @return object FakeStripeClient instance
     */
    public static function fake(array $fakes = []): object
    {
        if (!class_exists(\Tests\Support\FakeStripeClient::class)) {
            throw new \RuntimeException(
                "Stripe::fake() is only available in the test environment. " .
                'Make sure Tests\Support\FakeStripeClient exists.'
            );
        }

        $fakeClass = \Tests\Support\FakeStripeClient::class;
        $fake = new $fakeClass($fakes);

        // Bind to container so services will use it
        if (function_exists("app")) {
            app()->instance(\Stripe\StripeClient::class, $fake);
        }

        return $fake;
    }
}