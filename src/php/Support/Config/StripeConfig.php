<?php

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

    private function boot(): void
    {
        if (!$this->booted) {
            $this->authentication->publicKey = Config::get("services.stripe.public_key");
            $this->authentication->secretKey = Config::get("services.stripe.secret_key");

            $this->booted = true;
        }
    }

    public static function make(): StripeConfig
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}