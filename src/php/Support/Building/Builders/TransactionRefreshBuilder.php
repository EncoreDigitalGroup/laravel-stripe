<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Building\Builders;

use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeTransactionRefresh;

class TransactionRefreshBuilder
{
    public function build(mixed ...$params): StripeTransactionRefresh
    {
        return StripeTransactionRefresh::make(...$params);
    }
}