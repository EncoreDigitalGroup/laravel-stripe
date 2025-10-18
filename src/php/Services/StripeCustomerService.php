<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Services;

use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Stripe\Support\HasStripe;
use Illuminate\Support\Collection;
use Stripe\Exception\ApiErrorException;

/** @internal */
class StripeCustomerService
{
    use HasStripe;

    /** @throws ApiErrorException */
    public function create(StripeCustomer $customer): StripeCustomer
    {
        $data = $customer->toArray();

        // Remove id if present (can't send id on create)
        unset($data["id"]);

        $stripeCustomer = $this->stripe->customers->create($data);

        return StripeCustomer::fromStripeObject($stripeCustomer);
    }

    /** @throws ApiErrorException */
    public function get(string $customerId): StripeCustomer
    {
        $stripeCustomer = $this->stripe->customers->retrieve($customerId);

        return StripeCustomer::fromStripeObject($stripeCustomer);
    }

    /** @throws ApiErrorException */
    public function update(string $customerId, StripeCustomer $customer): StripeCustomer
    {
        $data = $customer->toArray();

        // Remove id from update data
        unset($data["id"]);

        $stripeCustomer = $this->stripe->customers->update($customerId, $data);

        return StripeCustomer::fromStripeObject($stripeCustomer);
    }

    /** @throws ApiErrorException */
    public function delete(string $customerId): bool
    {
        $result = $this->stripe->customers->delete($customerId);

        return $result->deleted ?? false;
    }

    /**
     * @return Collection<int, StripeCustomer>
     *
     * @throws ApiErrorException
     */
    public function list(array $params = []): Collection
    {
        $stripeCustomers = $this->stripe->customers->all($params);

        return collect($stripeCustomers->data)
            ->map(fn ($stripeCustomer): \EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer => StripeCustomer::fromStripeObject($stripeCustomer));
    }

    /**
     * @return Collection<int, StripeCustomer>
     *
     * @throws ApiErrorException
     */
    public function search(string $query, array $params = []): Collection
    {
        $params["query"] = $query;
        $stripeCustomers = $this->stripe->customers->search($params);

        return collect($stripeCustomers->data)
            ->map(fn ($stripeCustomer): \EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer => StripeCustomer::fromStripeObject($stripeCustomer));
    }
}