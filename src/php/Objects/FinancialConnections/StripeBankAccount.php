<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\FinancialConnections;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use PHPGenesis\Common\Traits\HasMake;

class StripeBankAccount
{
    use HasMake;
    use HasTimestamps;

    private ?string $id = null;
    private ?string $category = null;
    private ?CarbonImmutable $created = null;
    private ?string $displayName = null;
    private ?string $institutionName = null;
    private ?string $last4 = null;
    private ?bool $liveMode = null;
    private array $permissions = [];
    private array $subscriptions = [];
    private array $supportedPaymentMethodTypes = [];
    private ?StripeTransactionRefresh $transactionRefresh = null;

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "category" => $this->category,
            "created" => self::carbonToTimestamp($this->created),
            "display_name" => $this->displayName,
            "institution_name" => $this->institutionName,
            "last4" => $this->last4,
            "live_mode" => $this->liveMode,
            "permissions" => $this->permissions,
            "subscriptions" => $this->subscriptions,
            "supported_payment_method_types" => $this->supportedPaymentMethodTypes,
            "transaction_refresh" => $this->transactionRefresh?->toArray(),
        ];

        return Arr::whereNotNull($array);
    }

    // Fluent setters
    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function withCreated(CarbonImmutable $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function withDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function withInstitutionName(string $institutionName): self
    {
        $this->institutionName = $institutionName;

        return $this;
    }

    public function withLast4(string $last4): self
    {
        $this->last4 = $last4;

        return $this;
    }

    public function withLiveMode(bool $liveMode): self
    {
        $this->liveMode = $liveMode;

        return $this;
    }

    public function withPermissions(array $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function withSubscriptions(array $subscriptions): self
    {
        $this->subscriptions = $subscriptions;

        return $this;
    }

    public function withSupportedPaymentMethodTypes(array $supportedPaymentMethodTypes): self
    {
        $this->supportedPaymentMethodTypes = $supportedPaymentMethodTypes;

        return $this;
    }

    public function withTransactionRefresh(StripeTransactionRefresh $transactionRefresh): self
    {
        $this->transactionRefresh = $transactionRefresh;

        return $this;
    }

    // Getter methods
    public function id(): ?string
    {
        return $this->id;
    }

    public function category(): ?string
    {
        return $this->category;
    }

    public function created(): ?CarbonImmutable
    {
        return $this->created;
    }

    public function displayName(): ?string
    {
        return $this->displayName;
    }

    public function institutionName(): ?string
    {
        return $this->institutionName;
    }

    public function last4(): ?string
    {
        return $this->last4;
    }

    public function liveMode(): ?bool
    {
        return $this->liveMode;
    }

    public function permissions(): array
    {
        return $this->permissions;
    }

    public function subscriptions(): array
    {
        return $this->subscriptions;
    }

    public function supportedPaymentMethodTypes(): array
    {
        return $this->supportedPaymentMethodTypes;
    }

    public function transactionRefresh(): ?StripeTransactionRefresh
    {
        return $this->transactionRefresh;
    }
}