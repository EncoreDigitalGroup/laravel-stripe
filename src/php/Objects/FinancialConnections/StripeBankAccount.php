<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\FinancialConnections;

use Symfony\Component\Serializer\Attribute\SerializedName;

class StripeBankAccount
{
    public ?string $id = null;
    public ?string $category = null;
    public ?int $created = null;

    #[SerializedName("display_name")]
    public ?string $displayName = null;

    #[SerializedName("institution_name")]
    public ?string $institutionName = null;

    public ?string $last4 = null;

    #[SerializedName("livemode")]
    public ?bool $liveMode = null;

    public array $permissions = [];
    public array $subscriptions = [];

    #[SerializedName("supported_payment_method_types")]
    public array $supportedPaymentMethodTypes = [];

    #[SerializedName("transaction_refresh")]
    public ?string $transactionRefresh = null;
}