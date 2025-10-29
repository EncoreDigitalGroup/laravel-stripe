<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Services;

use EncoreDigitalGroup\Stripe\Objects\Product\StripeProduct;
use EncoreDigitalGroup\Stripe\Support\Traits\HasStripe;
use Illuminate\Support\Collection;
use Stripe\Exception\ApiErrorException;

/** @internal */
class StripeProductService
{
    use HasStripe;

    /** @throws ApiErrorException */
    public function create(StripeProduct $product): StripeProduct
    {
        $data = $product->toArray();

        // Remove id if present (can't send id on create)
        unset($data["id"], $data["created"], $data["updated"]);

        // Remove created/updated timestamps (read-only)

        /** @phpstan-ignore argument.type */
        $stripeProduct = $this->stripe->products->create($data);

        return StripeProduct::fromStripeObject($stripeProduct);
    }

    /** @throws ApiErrorException */
    public function get(string $productId): StripeProduct
    {
        $stripeProduct = $this->stripe->products->retrieve($productId);

        return StripeProduct::fromStripeObject($stripeProduct);
    }

    /** @throws ApiErrorException */
    public function update(string $productId, StripeProduct $product): StripeProduct
    {
        $data = $product->toArray();

        // Remove id from update data
        unset($data["id"], $data["created"], $data["updated"]);

        // Remove created/updated timestamps (read-only)

        $stripeProduct = $this->stripe->products->update($productId, $data);

        return StripeProduct::fromStripeObject($stripeProduct);
    }

    /** @throws ApiErrorException */
    public function delete(string $productId): bool
    {
        $result = $this->stripe->products->delete($productId);

        return $result->deleted ?? false;
    }

    /**
     * Archive a product (soft delete - sets active to false)
     *
     * @throws ApiErrorException
     */
    public function archive(string $productId): StripeProduct
    {
        $stripeProduct = $this->stripe->products->update($productId, [
            "active" => false,
        ]);

        return StripeProduct::fromStripeObject($stripeProduct);
    }

    /**
     * Reactivate an archived product
     *
     * @throws ApiErrorException
     */
    public function reactivate(string $productId): StripeProduct
    {
        $stripeProduct = $this->stripe->products->update($productId, [
            "active" => true,
        ]);

        return StripeProduct::fromStripeObject($stripeProduct);
    }

    /**
     * @return Collection<int, StripeProduct>
     *
     * @throws ApiErrorException
     */
    public function list(array $params = []): Collection
    {
        $stripeProducts = $this->stripe->products->all($params);

        return collect($stripeProducts->data)
            ->map(fn (\Stripe\Product $stripeProduct): StripeProduct => StripeProduct::fromStripeObject($stripeProduct));
    }

    /**
     * @return Collection<int, StripeProduct>
     *
     * @throws ApiErrorException
     */
    public function search(string $query, array $params = []): Collection
    {
        $params["query"] = $query;
        $stripeProducts = $this->stripe->products->search($params);

        return collect($stripeProducts->data)
            ->map(fn (\Stripe\Product $stripeProduct): StripeProduct => StripeProduct::fromStripeObject($stripeProduct));
    }
}