<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Support;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\StripeObject;

class StripeAddress
{
    use HasMake;

    private ?string $line1 = null;
    private ?string $line2 = null;
    private ?string $city = null;
    private ?string $state = null;
    private ?string $postalCode = null;
    private ?string $country = null;

    public static function fromStripeObject(StripeObject $stripeAddress): StripeAddress
    {
        $address = StripeAddress::make();

        if ($stripeAddress->line1 ?? null) {
            $address = $address->withLine1($stripeAddress->line1);
        }
        if ($stripeAddress->line2 ?? null) {
            $address = $address->withLine2($stripeAddress->line2);
        }
        if ($stripeAddress->city ?? null) {
            $address = $address->withCity($stripeAddress->city);
        }
        if ($stripeAddress->state ?? null) {
            $address = $address->withState($stripeAddress->state);
        }
        if ($stripeAddress->postal_code ?? null) {
            $address = $address->withPostalCode($stripeAddress->postal_code);
        }
        if ($stripeAddress->country ?? null) {
            return $address->withCountry($stripeAddress->country);
        }

        return $address;
    }

    public function toArray(): array
    {
        $array = [
            "line1" => $this->line1,
            "line2" => $this->line2,
            "city" => $this->city,
            "state" => $this->state,
            "postal_code" => $this->postalCode,
            "country" => $this->country,
        ];

        return Arr::whereNotNull($array);
    }

    // Fluent setters
    public function withLine1(string $line1): self
    {
        $this->line1 = $line1;

        return $this;
    }

    public function withLine2(string $line2): self
    {
        $this->line2 = $line2;

        return $this;
    }

    public function withCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function withState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function withPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function withCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    // Getter methods
    public function line1(): ?string
    {
        return $this->line1;
    }

    public function line2(): ?string
    {
        return $this->line2;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function state(): ?string
    {
        return $this->state;
    }

    public function postalCode(): ?string
    {
        return $this->postalCode;
    }

    public function country(): ?string
    {
        return $this->country;
    }
}