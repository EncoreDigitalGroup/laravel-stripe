<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use PHPGenesis\Common\Traits\HasMake;

class StripePhaseItem
{
    use HasMake;

    private string $price;
    private int $quantity = 1;
    private ?array $metadata = null;

    public function toArray(): array
    {
        return Arr::whereNotNull([
            "price" => $this->price,
            "quantity" => $this->quantity,
            "metadata" => $this->metadata,
        ]);
    }

    // Fluent setters
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

    public function withMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function price(): string
    {
        return $this->price;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function metadata(): ?array
    {
        return $this->metadata;
    }
}