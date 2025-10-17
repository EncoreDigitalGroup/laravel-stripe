<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\FinancialConnections;

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use PHPGenesis\Common\Traits\HasMake;

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