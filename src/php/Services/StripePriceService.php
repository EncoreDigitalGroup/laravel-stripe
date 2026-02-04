<?php

namespace EncoreDigitalGroup\Stripe\Services;

use EncoreDigitalGroup\Stripe\Objects\Product\StripePrice;
use EncoreDigitalGroup\Stripe\Support\Traits\HasStripe;
use Illuminate\Support\Collection;
use Stripe\Exception\ApiErrorException;
use Stripe\Price;

/** @internal */
class StripePriceService
{
    use HasStripe;

    /** @throws ApiErrorException */
    public function create(StripePrice $price): StripePrice
    {
        /** @phpstan-ignore argument.type */
        $stripePrice = $this->stripe->prices->create($price->toCreateArray());

        return StripePrice::fromStripeObject($stripePrice);
    }

    /** @throws ApiErrorException */
    public function get(string $priceId): StripePrice
    {
        $stripePrice = $this->stripe->prices->retrieve($priceId);

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
     * Update a price. Note: Only limited fields can be updated after creation
     * (active, metadata, nickname, lookup_key, tax_behavior)
     *
     * @throws ApiErrorException
     */
    public function update(string $priceId, StripePrice $price): StripePrice
    {
        $stripePrice = $this->stripe->prices->update($priceId, $price->toUpdateArray());

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
            ->map(fn(Price $stripePrice): StripePrice => StripePrice::fromStripeObject($stripePrice));
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
            ->map(fn(Price $stripePrice): StripePrice => StripePrice::fromStripeObject($stripePrice));
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