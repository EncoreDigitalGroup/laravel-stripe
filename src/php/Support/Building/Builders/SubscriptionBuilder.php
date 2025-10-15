<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use EncoreDigitalGroup\Stripe\Objects\Subscription\StripeSubscription;

class SubscriptionBuilder
{
    public function build(mixed ...$params): StripeSubscription
    {
        return StripeSubscription::make(...$params);
    }
}