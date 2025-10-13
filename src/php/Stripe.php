<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe;

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeFinancialConnection;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeWebhook;
use EncoreDigitalGroup\Stripe\Services\StripeCustomerService;
use EncoreDigitalGroup\Stripe\Support\HasStripe;
use EncoreDigitalGroup\Stripe\Support\Testing\FakeStripeClient;
use Stripe\StripeClient;

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

    public static function fake(array $fakes = []): FakeStripeClient
    {
        $fake = new FakeStripeClient($fakes);

        if (function_exists("app")) {
            app()->instance(StripeClient::class, $fake);
        }

        return $fake;
    }
}