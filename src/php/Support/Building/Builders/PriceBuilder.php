<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use EncoreDigitalGroup\Stripe\Objects\Product\StripePrice;

class PriceBuilder
{
    public function build(mixed ...$params): StripePrice
    {
        return StripePrice::make(...$params);
    }

    public function tier(): TierBuilder
    {
        return new TierBuilder;
    }

    public function customUnitAmount(): CustomUnitAmountBuilder
    {
        return new CustomUnitAmountBuilder;
    }

    public function recurring(): RecurringBuilder
    {
        return new RecurringBuilder;
    }
}