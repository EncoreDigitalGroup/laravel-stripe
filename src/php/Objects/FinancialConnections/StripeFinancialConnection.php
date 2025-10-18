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

    private ?StripeCustomer $customer = null;
    private array $permissions = [];

    public function toArray(): array
    {
        return [
            "account_holder" => [
                "type" => "customer",
                "customer" => $this->customer?->id(),
            ],
            "permissions" => $this->permissions,
        ];
    }

    // Fluent setters
    public function withCustomer(StripeCustomer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function withPermissions(array $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    // Getter methods
    public function customer(): ?StripeCustomer
    {
        return $this->customer;
    }

    public function permissions(): array
    {
        return $this->permissions;
    }
}