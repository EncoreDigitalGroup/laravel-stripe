<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building;

use EncoreDigitalGroup\Stripe\Support\Building\Builders\CustomUnitAmountBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\PriceBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\ProductBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\RecurringBuilder;
use EncoreDigitalGroup\Stripe\Support\Building\Builders\TierBuilder;

class StripeBuilder
{
    #region Main Entity Builders

    public function product(): ProductBuilder
    {
        return new ProductBuilder;
    }

    public function price(): PriceBuilder
    {
        return new PriceBuilder;
    }
    #endregion

    #region Sub-Object Builders

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
    #endregion
}