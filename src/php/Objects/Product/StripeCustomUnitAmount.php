<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Product;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use PHPGenesis\Common\Traits\HasMake;

class StripeCustomUnitAmount
{
    use HasMake;

    private ?int $minimum = null;
    private ?int $maximum = null;
    private ?int $preset = null;

    public function toArray(): array
    {
        $array = [
            "minimum" => $this->minimum,
            "maximum" => $this->maximum,
            "preset" => $this->preset,
        ];

        return Arr::whereNotNull($array);
    }

    // Fluent setters
    public function withMinimum(int $minimum): self
    {
        $this->minimum = $minimum;

        return $this;
    }

    public function withMaximum(int $maximum): self
    {
        $this->maximum = $maximum;

        return $this;
    }

    public function withPreset(int $preset): self
    {
        $this->preset = $preset;

        return $this;
    }

    // Getters
    public function minimum(): ?int
    {
        return $this->minimum;
    }

    public function maximum(): ?int
    {
        return $this->maximum;
    }

    public function preset(): ?int
    {
        return $this->preset;
    }
}