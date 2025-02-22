<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe;

use EncoreDigitalGroup\Common\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Common\Stripe\Objects\FinancialConnections\StripeFinancialConnection;
use EncoreDigitalGroup\Common\Stripe\Objects\Support\StripeWebhook;
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

    public static function webhook(): StripeWebhook
    {
        return StripeWebhook::make();
    }
}