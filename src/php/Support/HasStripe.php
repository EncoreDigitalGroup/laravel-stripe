<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Support;

use EncoreDigitalGroup\Common\Stripe\Stripe;
use EncoreDigitalGroup\Common\Stripe\Support\Config\StripeConfig;
use EncoreDigitalGroup\StdLib\Exceptions\NullExceptions\ClassPropertyNullException;
use Stripe\StripeClient;

trait HasStripe
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $config = self::config();

        if (is_null($config->authentication->secretKey)) {
            throw new ClassPropertyNullException("secretKey");
        }

        $this->stripe = new StripeClient($config->authentication->secretKey);
    }

    /**
     * @return static
     */
    public static function make(): static
    {
        return new static;
    }

    public static function config(): StripeConfig
    {
        return StripeConfig::make();
    }

    public static function client(): StripeClient
    {
        return self::make()->stripe;
    }
}