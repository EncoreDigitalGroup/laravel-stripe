<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;

class CustomerBuilder
{
    public function build(mixed ...$params): StripeCustomer
    {
        return StripeCustomer::make(...$params);
    }

    public function address(): AddressBuilder
    {
        return new AddressBuilder;
    }

    public function shipping(): ShippingBuilder
    {
        return new ShippingBuilder;
    }
}