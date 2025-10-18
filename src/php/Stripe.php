<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe;

use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Stripe\Objects\Webhook\StripeWebhookEndpoint;
use EncoreDigitalGroup\Stripe\Services\StripeCustomerService;
use EncoreDigitalGroup\Stripe\Services\StripePriceService;
use EncoreDigitalGroup\Stripe\Services\StripeProductService;
use EncoreDigitalGroup\Stripe\Services\StripeSubscriptionScheduleService;
use EncoreDigitalGroup\Stripe\Services\StripeSubscriptionService;
use EncoreDigitalGroup\Stripe\Services\StripeWebhookEndpointService;
use EncoreDigitalGroup\Stripe\Support\Building\StripeBuilder;
use EncoreDigitalGroup\Stripe\Support\HasStripe;
use EncoreDigitalGroup\Stripe\Support\Testing\FakeStripeClient;
use Stripe\StripeClient;

class Stripe
{
    use HasStripe;

    #region Shortcuts

    public static function subscription(): StripeSubscription
    {
        return StripeSubscription::make();
    }

    public static function webhook(): StripeWebhookEndpoint
    {
        return StripeWebhookEndpoint::make();
    }

    #endregion

    #region Builder Methods - Access fluent builders

    public static function builder(): StripeBuilder
    {
        return new StripeBuilder;
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

    public static function subscriptionSchedules(): StripeSubscriptionScheduleService
    {
        return StripeSubscriptionScheduleService::make();
    }

    public static function webhookEndpoints(): StripeWebhookEndpointService
    {
        return StripeWebhookEndpointService::make();
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