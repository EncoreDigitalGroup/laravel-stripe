<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeShipping;

class ShippingBuilder
{
    public function build(mixed ...$params): StripeShipping
    {
        return StripeShipping::make(...$params);
    }

    public function address(): AddressBuilder
    {
        return new AddressBuilder;
    }
}