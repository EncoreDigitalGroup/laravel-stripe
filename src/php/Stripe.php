<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe;

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeFinancialConnection;
use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Stripe\Objects\Webhook\StripeWebhookEndpoint;
use EncoreDigitalGroup\Stripe\Support\Testing\FakeStripeClient;
use EncoreDigitalGroup\Stripe\Support\Traits\HasStripe;
use Stripe\StripeClient;

class Stripe
{
    use HasStripe;

    #region Shortcuts

    public static function customer(): StripeCustomer
    {
        return StripeCustomer::make();
    }

    public static function subscription(): StripeSubscription
    {
        return StripeSubscription::make();
    }

    public static function webhook(): StripeWebhookEndpoint
    {
        return StripeWebhookEndpoint::make();
    }

    public static function financialConnection(): StripeFinancialConnection
    {
        return StripeFinancialConnection::make();
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