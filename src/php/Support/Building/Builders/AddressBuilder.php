<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;

class AddressBuilder
{
    public function build(mixed ...$params): StripeAddress
    {
        return StripeAddress::make(...$params);
    }
}