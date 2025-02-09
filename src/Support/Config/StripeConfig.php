<?php

namespace EncoreDigitalGroup\Common\Stripe\Support\Config;

class StripeConfig
{
    private static self $instance;

    public Authentication $authentication;

    public static function make(): StripeConfig
    {
        if(!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->authentication = new Authentication;
    }
}