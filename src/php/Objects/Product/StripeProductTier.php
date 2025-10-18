<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Product;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use PHPGenesis\Common\Traits\HasMake;

class StripeProductTier
{
    use HasMake;

    private int|string|null $upTo = null;
    private ?int $unitAmount = null;
    private ?string $unitAmountDecimal = null;
    private ?int $flatAmount = null;
    private ?string $flatAmountDecimal = null;

    public function toArray(): array
    {
        $array = [
            "up_to" => $this->upTo,
            "unit_amount" => $this->unitAmount,
            "unit_amount_decimal" => $this->unitAmountDecimal,
            "flat_amount" => $this->flatAmount,
            "flat_amount_decimal" => $this->flatAmountDecimal,
        ];

        return Arr::whereNotNull($array);
    }

    // Fluent setters
    public function withUpTo(int|string $upTo): self
    {
        $this->upTo = $upTo;

        return $this;
    }

    public function withUnitAmount(int $unitAmount): self
    {
        $this->unitAmount = $unitAmount;

        return $this;
    }

    public function withUnitAmountDecimal(string $unitAmountDecimal): self
    {
        $this->unitAmountDecimal = $unitAmountDecimal;

        return $this;
    }

    public function withFlatAmount(int $flatAmount): self
    {
        $this->flatAmount = $flatAmount;

        return $this;
    }

    public function withFlatAmountDecimal(string $flatAmountDecimal): self
    {
        $this->flatAmountDecimal = $flatAmountDecimal;

        return $this;
    }

    // Getters
    public function upTo(): int|string|null
    {
        return $this->upTo;
    }

    public function unitAmount(): ?int
    {
        return $this->unitAmount;
    }

    public function unitAmountDecimal(): ?string
    {
        return $this->unitAmountDecimal;
    }

    public function flatAmount(): ?int
    {
        return $this->flatAmount;
    }

    public function flatAmountDecimal(): ?string
    {
        return $this->flatAmountDecimal;
    }
}