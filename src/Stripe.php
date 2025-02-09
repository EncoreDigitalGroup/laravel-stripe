<?php

namespace EncoreDigitalGroup\Common\Stripe;

use EncoreDigitalGroup\Common\Stripe\Support\Config\StripeConfig;

class Stripe
{
    public static function config(): StripeConfig
    {
        return StripeConfig::make();
    }
}