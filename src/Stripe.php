<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe;

use EncoreDigitalGroup\Common\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Common\Stripe\Support\HasStripe;

class Stripe
{
    use HasStripe;

    public static function customer(mixed ...$params): StripeCustomer
    {
        return StripeCustomer::make(...$params);
    }
}