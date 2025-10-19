<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Config;

use Illuminate\Support\Facades\Config;

class StripeConfig
{
    private static self $instance;
    public Authentication $authentication;
    private bool $booted = false;

    public function __construct()
    {
        $this->authentication = new Authentication;
        $this->boot();
    }

    public static function make(): StripeConfig
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function boot(): void
    {
        if (!$this->booted) {
            $this->authentication->publicKey = Config::get("services.stripe.public_key");
            $this->authentication->secretKey = Config::get("services.stripe.secret_key");

            $this->booted = true;
        }
    }
}