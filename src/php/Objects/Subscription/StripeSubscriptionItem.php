<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Subscription;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use PHPGenesis\Common\Traits\HasMake;

class StripeSubscriptionItem
{
    use HasMake;

    private ?string $id = null;
    private ?string $price = null;
    private int $quantity = 1;
    private ?CarbonImmutable $currentPeriodStart = null;
    private ?CarbonImmutable $currentPeriodEnd = null;
    private ?array $metadata = null;

    public function toArray(): array
    {
        return Arr::whereNotNull([
            "id" => $this->id,
            "price" => $this->price,
            "quantity" => $this->quantity,
            "current_period_start" => $this->currentPeriodStart?->getTimestamp(),
            "current_period_end" => $this->currentPeriodEnd?->getTimestamp(),
            "metadata" => $this->metadata,
        ]);
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function withQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function withCurrentPeriodStart(CarbonImmutable $currentPeriodStart): self
    {
        $this->currentPeriodStart = $currentPeriodStart;

        return $this;
    }

    public function withCurrentPeriodEnd(CarbonImmutable $currentPeriodEnd): self
    {
        $this->currentPeriodEnd = $currentPeriodEnd;

        return $this;
    }

    public function withMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function price(): ?string
    {
        return $this->price;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function currentPeriodStart(): ?CarbonImmutable
    {
        return $this->currentPeriodStart;
    }

    public function currentPeriodEnd(): ?CarbonImmutable
    {
        return $this->currentPeriodEnd;
    }

    public function metadata(): ?array
    {
        return $this->metadata;
    }
}