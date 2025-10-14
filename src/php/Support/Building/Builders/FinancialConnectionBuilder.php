<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeFinancialConnection;

class FinancialConnectionBuilder
{
    public function build(mixed ...$params): StripeFinancialConnection
    {
        return StripeFinancialConnection::make(...$params);
    }

    public function bankAccount(): BankAccountBuilder
    {
        return new BankAccountBuilder();
    }

    public function transactionRefresh(): TransactionRefreshBuilder
    {
        return new TransactionRefreshBuilder();
    }
}