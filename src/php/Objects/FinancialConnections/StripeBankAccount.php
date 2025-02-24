<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\FinancialConnections;

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

    public array $permissions;
    public array $subscriptions;

    #[SerializedName("supported_payment_method_types")]
    public array $supportedPaymentMethodTypes;

    #[SerializedName("transaction_refresh")]
    public ?string $transactionRefresh;
}