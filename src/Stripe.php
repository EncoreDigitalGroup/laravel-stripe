<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe;

use EncoreDigitalGroup\Common\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Common\Stripe\Support\HasStripe;
use PHPGenesis\Logger\Logger;
use Stripe\Exception\ApiErrorException;
use Stripe\FinancialConnections\Session as FinancialConnectionsSession;

class Stripe
{
    use HasStripe;

    public static function customer(mixed ...$params): StripeCustomer
    {
        return StripeCustomer::make(...$params);
    }

    public function createFinancialConnectionsSession(StripeCustomer $customer): ?FinancialConnectionsSession
    {
        try {
            return $this
                ->stripe
                ->financialConnections
                ->sessions
                ->create($customer->toArray());
        } catch (ApiErrorException $exception) {
            Logger::error("[Stripe] Exception {exception.message}", [
                "exception.message" => $exception->getMessage(),
            ]);
        }

        return null;
    }
}