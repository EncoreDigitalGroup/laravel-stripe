<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Services;

use EncoreDigitalGroup\Stripe\Objects\Product\StripePrice;
use EncoreDigitalGroup\Stripe\Support\HasStripe;
use Illuminate\Support\Collection;
use Stripe\Exception\ApiErrorException;

/** @internal */
class StripePriceService
{
    use HasStripe;

    /** @throws ApiErrorException */
    public function create(StripePrice $price): StripePrice
    {
        $data = $price->toArray();

        // Remove id if present (can't send id on create)
        unset($data["id"], $data["created"]);

        // Remove created timestamp (read-only)

        /** @phpstan-ignore argument.type */
        $stripePrice = $this->stripe->prices->create($data);

        return StripePrice::fromStripeObject($stripePrice);
    }

    /** @throws ApiErrorException */
    public function get(string $priceId): StripePrice
    {
        $stripePrice = $this->stripe->prices->retrieve($priceId);

        return StripePrice::fromStripeObject($stripePrice);
    }

    /**
     * Update a price. Note: Only limited fields can be updated after creation
     * (active, metadata, nickname, lookup_key, tax_behavior)
     *
     * @throws ApiErrorException
     */
    public function update(string $priceId, StripePrice $price): StripePrice
    {
        $data = $price->toArray();

        // Remove id from update data
        unset($data["id"], $data["created"],
            $data["product"],
            $data["currency"],
            $data["unit_amount"],
            $data["unit_amount_decimal"],
            $data["type"],
            $data["billing_scheme"],
            $data["recurring"],
            $data["tiers"],
            $data["tiers_mode"],
            $data["transform_quantity"],
            $data["custom_unit_amount"]
        );

        // Remove created timestamp (read-only)

        // Remove immutable fields that can't be updated

        $stripePrice = $this->stripe->prices->update($priceId, $data);

        return StripePrice::fromStripeObject($stripePrice);
    }

    /**
     * Archive a price (soft delete - sets active to false)
     * Prices cannot be deleted in Stripe, only archived
     *
     * @throws ApiErrorException
     */
    public function archive(string $priceId): StripePrice
    {
        $stripePrice = $this->stripe->prices->update($priceId, [
            "active" => false,
        ]);

        return StripePrice::fromStripeObject($stripePrice);
    }

    /**
     * Reactivate an archived price
     *
     * @throws ApiErrorException
     */
    public function reactivate(string $priceId): StripePrice
    {
        $stripePrice = $this->stripe->prices->update($priceId, [
            "active" => true,
        ]);

        return StripePrice::fromStripeObject($stripePrice);
    }

    /**
     * Get all prices for a specific product
     *
     * @return Collection<int, StripePrice>
     *
     * @throws ApiErrorException
     */
    public function listByProduct(string $productId, array $params = []): Collection
    {
        $params["product"] = $productId;

        return $this->list($params);
    }

    /**
     * @return Collection<int, StripePrice>
     *
     * @throws ApiErrorException
     */
    public function list(array $params = []): Collection
    {
        $stripePrices = $this->stripe->prices->all($params);

        return collect($stripePrices->data)
            ->map(fn ($stripePrice): StripePrice => StripePrice::fromStripeObject($stripePrice));
    }

    /**
     * @return Collection<int, StripePrice>
     *
     * @throws ApiErrorException
     */
    public function search(string $query, array $params = []): Collection
    {
        $params["query"] = $query;
        $stripePrices = $this->stripe->prices->search($params);

        return collect($stripePrices->data)
            ->map(fn ($stripePrice): StripePrice => StripePrice::fromStripeObject($stripePrice));
    }

    /**
     * Get a price by its lookup key
     *
     * @throws ApiErrorException
     */
    public function getByLookupKey(string $lookupKey): ?StripePrice
    {
        $prices = $this->list(["lookup_keys" => [$lookupKey]]);

        return $prices->first();
    }
}