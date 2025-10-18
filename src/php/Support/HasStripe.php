<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support;

use EncoreDigitalGroup\StdLib\Exceptions\NullExceptions\ClassPropertyNullException;
use EncoreDigitalGroup\Stripe\Support\Config\StripeConfig;
use Stripe\StripeClient;

trait HasStripe
{
    protected StripeClient $stripe;

    public function __construct(?StripeClient $client = null)
    {
        if ($client instanceof StripeClient) {
            $this->stripe = $client;

            return;
        }


        if (function_exists("app") && app()->bound(StripeClient::class)) {
            $this->stripe = app(StripeClient::class);

            return;
        }

        // Default behavior: create new client with API key
        $config = self::config();

        if (is_null($config->authentication->secretKey)) {
            throw new ClassPropertyNullException("secretKey");
        }

        $this->stripe = new StripeClient($config->authentication->secretKey);
    }

    public static function make(?StripeClient $client = null): static
    {
        return new static($client);
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