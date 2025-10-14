<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use EncoreDigitalGroup\Stripe\Objects\Product\StripeProduct;

class ProductBuilder
{
    public function build(mixed ...$params): StripeProduct
    {
        return StripeProduct::make(...$params);
    }

    public function tier(): TierBuilder
    {
        return new TierBuilder();
    }

    public function customUnitAmount(): CustomUnitAmountBuilder
    {
        return new CustomUnitAmountBuilder();
    }
}