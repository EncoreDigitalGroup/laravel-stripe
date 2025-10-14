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
use EncoreDigitalGroup\Stripe\Objects\Product\StripeRecurring;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeWebhook;
use EncoreDigitalGroup\Stripe\Services\StripeCustomerService;
use EncoreDigitalGroup\Stripe\Services\StripePriceService;
use EncoreDigitalGroup\Stripe\Services\StripeProductService;
use EncoreDigitalGroup\Stripe\Services\StripeSubscriptionService;
use EncoreDigitalGroup\Stripe\Support\Building\StripeBuilder;
use EncoreDigitalGroup\Stripe\Support\HasStripe;
use EncoreDigitalGroup\Stripe\Support\Testing\FakeStripeClient;
use Stripe\StripeClient;

class Stripe
{
    use HasStripe;

    #region Factory Methods - Create data objects using builders

    public static function customer(mixed ...$params): StripeCustomer
    {
        return self::builder()->customer()->build(...$params);
    }

    public static function product(mixed ...$params): StripeProduct
    {
        return self::builder()->product()->build(...$params);
    }

    public static function price(mixed ...$params): StripePrice
    {
        return self::builder()->price()->build(...$params);
    }

    public static function recurring(mixed ...$params): StripeRecurring
    {
        return self::builder()->recurring()->build(...$params);
    }

    public static function subscription(mixed ...$params): StripeSubscription
    {
        return self::builder()->subscription()->build(...$params);
    }

    public static function address(mixed ...$params): StripeAddress
    {
        return self::builder()->address()->build(...$params);
    }

    public static function financialConnections(mixed ...$params): StripeFinancialConnection
    {
        return self::builder()->financialConnection()->build(...$params);
    }

    public static function webhook(mixed ...$params): StripeWebhook
    {
        return self::builder()->webhook()->build(...$params);
    }

    #endregion

    #region Builder Methods - Access fluent builders

    public static function builder(): StripeBuilder
    {
        return new StripeBuilder();
    }

    #endregion

    #region Service Accessor Methods - Get service instances

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

    #endregion

    #region Testing Method

    public static function fake(array $fakes = []): FakeStripeClient
    {
        $fake = new FakeStripeClient($fakes);

        if (function_exists("app")) {
            app()->instance(StripeClient::class, $fake);
        }

        return $fake;
    }

    #endregion
}