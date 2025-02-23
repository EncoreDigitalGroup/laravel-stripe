<?php
/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\FinancialConnections;

use Illuminate\Support\Collection;
use Symfony\Component\Serializer\Attribute\SerializedName;

class StripeBankAccount
{
    public ?string $id;
    public ?string $category;
    public ?int $created;

    #[SerializedName("display_name")]
    public ?string $displayName;

    #[SerializedName("institution_name")]
    public ?string $institutionName;
    public ?string $last4;

    #[SerializedName("livemode")]
    public ?bool $liveMode;
    public Collection $permissions;
    public Collection $subscriptions;

    #[SerializedName("supported_payment_method_types")]
    public Collection $supportedPaymentMethodTypes;

    #[SerializedName("transaction_refresh")]
    public ?string $transactionRefresh;
}