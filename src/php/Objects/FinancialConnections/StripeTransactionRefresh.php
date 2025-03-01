<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\FinancialConnections;

class StripeTransactionRefresh
{
    public ?string $id = null;
    public ?int $lastAttemptedAt = null;
    public ?int $nextRefreshAvailableAt = null;
    public ?string $status = null;
}