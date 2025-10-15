<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Product;

use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * @extends Collection<int, StripeProductTier>
 */
class StripeProductTierCollection extends Collection
{
    public function __construct(array $items = [])
    {
        // Ensure all items are StripeProductTier instances
        $validatedItems = array_map(function ($item): \EncoreDigitalGroup\Stripe\Objects\Product\StripeProductTier {
            if ($item instanceof StripeProductTier) {
                return $item;
            }

            if (is_array($item)) {
                return StripeProductTier::make(
                    upTo: $item["up_to"] ?? $item["upTo"] ?? null,
                    unitAmount: $item["unit_amount"] ?? $item["unitAmount"] ?? null,
                    unitAmountDecimal: $item["unit_amount_decimal"] ?? $item["unitAmountDecimal"] ?? null,
                    flatAmount: $item["flat_amount"] ?? $item["flatAmount"] ?? null,
                    flatAmountDecimal: $item["flat_amount_decimal"] ?? $item["flatAmountDecimal"] ?? null
                );
            }

            throw new InvalidArgumentException("Collection items must be StripeProductTier instances or arrays");
        }, $items);

        parent::__construct($validatedItems);
    }

    public static function fromArray(array $tiers): self
    {
        return new self($tiers);
    }

    /** Convert the collection to an array suitable for API requests. */
    public function toArray(): array
    {
        return $this->map(fn(StripeProductTier $tier): array => $tier->toArray())->values()->toArray();
    }

    /**
     * Add a tier to the collection.
     */
    public function addTier(
        int|string $upTo,
        ?int       $unitAmount = null,
        ?string    $unitAmountDecimal = null,
        ?int       $flatAmount = null,
        ?string    $flatAmountDecimal = null
    ): self
    {
        $tier = StripeProductTier::make(
            upTo: $upTo,
            unitAmount: $unitAmount,
            unitAmountDecimal: $unitAmountDecimal,
            flatAmount: $flatAmount,
            flatAmountDecimal: $flatAmountDecimal
        );

        return $this->push($tier);
    }

    /**
     * Get tiers up to a specific limit.
     */
    public function upTo(int|string $limit): self
    {
        return $this->filter(function (StripeProductTier $tier) use ($limit): bool {
            if ($tier->upTo === "inf") {
                return true;
            }

            if (is_numeric($limit) && is_numeric($tier->upTo)) {
                return $tier->upTo <= $limit;
            }

            return false;
        });
    }

    /**
     * Get the infinite tier (if exists).
     */
    public function infiniteTier(): ?StripeProductTier
    {
        return $this->firstWhere("upTo", "inf");
    }

    /**
     * Get tiers with flat amounts.
     */
    public function withFlatAmounts(): self
    {
        return $this->filter(fn(StripeProductTier $tier): bool => !is_null($tier->flatAmount) || !is_null($tier->flatAmountDecimal)
        );
    }

    /**
     * Get tiers with unit amounts.
     */
    public function withUnitAmounts(): self
    {
        return $this->filter(fn(StripeProductTier $tier): bool => !is_null($tier->unitAmount) || !is_null($tier->unitAmountDecimal)
        );
    }
}