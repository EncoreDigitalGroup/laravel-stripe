<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeBankAccount;

class BankAccountBuilder
{
    public function build(mixed ...$params): StripeBankAccount
    {
        return StripeBankAccount::make(...$params);
    }
}