<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use EncoreDigitalGroup\Stripe\Objects\Product\StripeProductTier;

class TierBuilder
{
    public function build(mixed ...$params): StripeProductTier
    {
        return StripeProductTier::make(...$params);
    }
}