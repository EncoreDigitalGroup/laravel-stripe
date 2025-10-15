<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use EncoreDigitalGroup\Stripe\Objects\Product\StripeCustomUnitAmount;

class CustomUnitAmountBuilder
{
    public function build(mixed ...$params): StripeCustomUnitAmount
    {
        return StripeCustomUnitAmount::make(...$params);
    }
}