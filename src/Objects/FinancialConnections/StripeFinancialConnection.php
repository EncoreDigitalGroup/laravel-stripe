<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\FinancialConnections;

use EncoreDigitalGroup\Common\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Common\Stripe\Support\HasMake;

class StripeFinancialConnection
{
    use HasMake;

    public function __construct(
        public StripeCustomer $customer,
        public array $permissions = ["transactions"]
    ) {}

    public function toArray(): array
    {
        return [
            "account_holder" => [
                "type" => "customer",
                "customer" => $this->customer->id,
            ],
            "permissions" => $this->permissions,
        ];
    }
}