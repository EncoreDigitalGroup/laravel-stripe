<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Services;

use EncoreDigitalGroup\Common\Stripe\Objects\Customer\StripeCustomer;
use EncoreDigitalGroup\Common\Stripe\Support\HasStripe;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;

class StripeCustomerService
{
    use HasStripe;

    /**
     * Create a new customer in Stripe
     *
     * @throws ApiErrorException
     */
    public function create(StripeCustomer $customer): StripeCustomer
    {
        $data = $customer->toArray();

        // Remove id if present (can't send id on create)
        unset($data["id"]);

        $stripeCustomer = $this->stripe->customers->create($data);

        return StripeCustomer::fromStripeObject($stripeCustomer);
    }

    /**
     * Retrieve a customer from Stripe
     *
     * @throws ApiErrorException
     */
    public function get(string $customerId): StripeCustomer
    {
        $stripeCustomer = $this->stripe->customers->retrieve($customerId);

        return StripeCustomer::fromStripeObject($stripeCustomer);
    }

    /**
     * Update an existing customer in Stripe
     *
     * @throws ApiErrorException
     */
    public function update(string $customerId, StripeCustomer $customer): StripeCustomer
    {
        $data = $customer->toArray();

        // Remove id from update data
        unset($data["id"]);

        $stripeCustomer = $this->stripe->customers->update($customerId, $data);

        return StripeCustomer::fromStripeObject($stripeCustomer);
    }

    /**
     * Delete a customer from Stripe
     *
     * @throws ApiErrorException
     */
    public function delete(string $customerId): bool
    {
        $result = $this->stripe->customers->delete($customerId);

        return $result->deleted ?? false;
    }

    /**
     * List all customers from Stripe with optional filters
     *
     * @param  array  $params  Optional parameters (limit, starting_after, ending_before, email, etc.)
     * @return array<StripeCustomer>
     *
     * @throws ApiErrorException
     */
    public function list(array $params = []): array
    {
        $stripeCustomers = $this->stripe->customers->all($params);

        $customers = [];
        foreach ($stripeCustomers->data as $stripeCustomer) {
            $customers[] = StripeCustomer::fromStripeObject($stripeCustomer);
        }

        return $customers;
    }

    /**
     * Search for customers using Stripe's search API
     *
     * @param  string  $query  Search query (e.g., "email:'customer@example.com'")
     * @param  array  $params  Optional parameters (limit, page, etc.)
     * @return array<StripeCustomer>
     *
     * @throws ApiErrorException
     */
    public function search(string $query, array $params = []): array
    {
        $params["query"] = $query;
        $stripeCustomers = $this->stripe->customers->search($params);

        $customers = [];
        foreach ($stripeCustomers->data as $stripeCustomer) {
            $customers[] = StripeCustomer::fromStripeObject($stripeCustomer);
        }

        return $customers;
    }
}