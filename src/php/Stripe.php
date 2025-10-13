<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe;

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeFinancialConnection;
use EncoreDigitalGroup\Stripe\Objects\Product\StripePrice;
use EncoreDigitalGroup\Stripe\Objects\Product\StripeProduct;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeWebhook;
use EncoreDigitalGroup\Stripe\Services\StripeCustomerService;
use EncoreDigitalGroup\Stripe\Services\StripePriceService;
use EncoreDigitalGroup\Stripe\Services\StripeProductService;
use EncoreDigitalGroup\Stripe\Services\StripeSubscriptionService;
use EncoreDigitalGroup\Stripe\Support\HasStripe;
use EncoreDigitalGroup\Stripe\Support\Testing\FakeStripeClient;
use Stripe\StripeClient;

class Stripe
{
    use HasStripe;

    // Factory Methods - Create data objects

    public static function customer(mixed ...$params): StripeCustomer
    {
        return StripeCustomer::make(...$params);
    }

    public static function product(mixed ...$params): StripeProduct
    {
        return StripeProduct::make(...$params);
    }

    public static function price(mixed ...$params): StripePrice
    {
        return StripePrice::make(...$params);
    }

    public static function subscription(mixed ...$params): StripeSubscription
    {
        return StripeSubscription::make(...$params);
    }

    public static function address(mixed ...$params): StripeAddress
    {
        return StripeAddress::make(...$params);
    }

    public static function financialConnections(mixed ...$params): StripeFinancialConnection
    {
        return StripeFinancialConnection::make(...$params);
    }

    public static function webhook(mixed ...$params): StripeWebhook
    {
        return StripeWebhook::make(...$params);
    }

    // Service Accessor Methods - Get service instances

    public static function customers(): StripeCustomerService
    {
        return StripeCustomerService::make();
    }

    public static function products(): StripeProductService
    {
        return StripeProductService::make();
    }

    public static function prices(): StripePriceService
    {
        return StripePriceService::make();
    }

    public static function subscriptions(): StripeSubscriptionService
    {
        return StripeSubscriptionService::make();
    }

    // Testing Method

    public static function fake(array $fakes = []): FakeStripeClient
    {
        $fake = new FakeStripeClient($fakes);

        if (function_exists("app")) {
            app()->instance(StripeClient::class, $fake);
        }

        return $fake;
    }
}