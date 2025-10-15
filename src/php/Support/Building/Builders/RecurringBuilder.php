<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use EncoreDigitalGroup\Stripe\Objects\Product\StripeRecurring;

class RecurringBuilder
{
    public function build(mixed ...$params): StripeRecurring
    {
        return StripeRecurring::make(...$params);
    }
}