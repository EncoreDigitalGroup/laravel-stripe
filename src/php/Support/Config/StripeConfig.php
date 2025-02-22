<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Support\Config;

class StripeConfig
{
    private static self $instance;
    public Authentication $authentication;

    public function __construct()
    {
        $this->authentication = new Authentication;
    }

    public static function make(): StripeConfig
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}