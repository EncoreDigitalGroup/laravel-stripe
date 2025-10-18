<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Customer;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeAddress;
use PHPGenesis\Common\Traits\HasMake;

class StripeShipping
{
    use HasMake;

    private ?StripeAddress $address = null;
    private ?string $name = null;
    private ?string $phone = null;

    public function toArray(): array
    {
        $array = [
            "address" => $this->address?->toArray(),
            "name" => $this->name,
            "phone" => $this->phone,
        ];

        return Arr::whereNotNull($array);
    }

    // Fluent setters
    public function withAddress(StripeAddress $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    // Getter methods
    public function address(): ?StripeAddress
    {
        return $this->address;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }
}