<?php
/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\FinancialConnections;

use EncoreDigitalGroup\Common\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Common\Stripe\Support\HasMake;
use PHPGenesis\Logger\Logger;
use Stripe\Exception\ApiErrorException;
use Stripe\FinancialConnections\Session as FinancialConnectionsSession;
use Stripe\StripeClient;

class StripeFinancialConnection
{
    use HasMake;

    public function __construct(
        public StripeClient   $stripe,
        public StripeCustomer $customer,
        public array          $permissions = ["transactions"]
    )
    {
    }

    public function create(): ?FinancialConnectionsSession
    {
        try {
            return $this
                ->stripe
                ->financialConnections
                ->sessions
                ->create($this->customer->toArray());
        } catch (ApiErrorException $exception) {
            Logger::error("[Stripe] Exception {exception.message}", [
                "exception.message" => $exception->getMessage(),
            ]);
        }

        return null;
    }

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