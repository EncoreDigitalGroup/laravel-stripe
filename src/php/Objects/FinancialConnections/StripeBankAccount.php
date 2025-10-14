<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\FinancialConnections;

class StripeBankAccount
{
    public ?string $id = null;
    public ?string $category = null;
    public ?int $created = null;
    public ?string $displayName = null;
    public ?string $institutionName = null;
    public ?string $last4 = null;
    public ?bool $liveMode = null;
    public array $permissions = [];
    public array $subscriptions = [];
    public array $supportedPaymentMethodTypes = [];
    public ?StripeTransactionRefresh $transactionRefresh = null;
}