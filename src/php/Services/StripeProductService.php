<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Services;

use EncoreDigitalGroup\Common\Stripe\Objects\Product\StripeProduct;
use EncoreDigitalGroup\Common\Stripe\Support\HasStripe;
use Illuminate\Support\Collection;
use Stripe\Exception\ApiErrorException;

class StripeProductService
{
    use HasStripe;

    /** @throws ApiErrorException */
    public function create(StripeProduct $product): StripeProduct
    {
        $data = $product->toArray();

        // Remove id if present (can't send id on create)
        unset($data['id']);

        // Remove created/updated timestamps (read-only)
        unset($data['created'], $data['updated']);

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
        unset($data['id']);

        // Remove created/updated timestamps (read-only)
        unset($data['created'], $data['updated']);

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
            'active' => false,
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
            'active' => true,
        ]);

        return StripeProduct::fromStripeObject($stripeProduct);
    }

    /**
     * @return Collection<int, StripeProduct>
     * @throws ApiErrorException
     */
    public function list(array $params = []): Collection
    {
        $stripeProducts = $this->stripe->products->all($params);

        return collect($stripeProducts->data)
            ->map(fn($stripeProduct) => StripeProduct::fromStripeObject($stripeProduct));
    }

    /**
     * @return Collection<int, StripeProduct>
     * @throws ApiErrorException
     */
    public function search(string $query, array $params = []): Collection
    {
        $params['query'] = $query;
        $stripeProducts = $this->stripe->products->search($params);

        return collect($stripeProducts->data)
            ->map(fn($stripeProduct) => StripeProduct::fromStripeObject($stripeProduct));
    }
}