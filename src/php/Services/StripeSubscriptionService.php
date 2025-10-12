<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Services;

use EncoreDigitalGroup\Common\Stripe\Objects\Subscription\StripeSubscription;
use EncoreDigitalGroup\Common\Stripe\Support\HasStripe;
use Illuminate\Support\Collection;
use Stripe\Exception\ApiErrorException;

class StripeSubscriptionService
{
    use HasStripe;

    /** @throws ApiErrorException */
    public function create(StripeSubscription $subscription): StripeSubscription
    {
        $data = $subscription->toArray();

        // Remove id if present (can't send id on create)
        unset($data["id"]);

        /** @phpstan-ignore argument.type */
        $stripeSubscription = $this->stripe->subscriptions->create($data);

        return StripeSubscription::fromStripeObject($stripeSubscription);
    }

    /** @throws ApiErrorException */
    public function get(string $subscriptionId): StripeSubscription
    {
        $stripeSubscription = $this->stripe->subscriptions->retrieve($subscriptionId);

        return StripeSubscription::fromStripeObject($stripeSubscription);
    }

    /** @throws ApiErrorException */
    public function update(string $subscriptionId, StripeSubscription $subscription): StripeSubscription
    {
        $data = $subscription->toArray();

        // Remove id from update data
        unset($data["id"]);

        $stripeSubscription = $this->stripe->subscriptions->update($subscriptionId, $data);

        return StripeSubscription::fromStripeObject($stripeSubscription);
    }

    /** @throws ApiErrorException */
    public function cancel(string $subscriptionId): StripeSubscription
    {
        $stripeSubscription = $this->stripe->subscriptions->cancel($subscriptionId);

        return StripeSubscription::fromStripeObject($stripeSubscription);
    }

    /** @throws ApiErrorException */
    public function cancelAtPeriodEnd(string $subscriptionId): StripeSubscription
    {
        $stripeSubscription = $this->stripe->subscriptions->update($subscriptionId, [
            "cancel_at_period_end" => true,
        ]);

        return StripeSubscription::fromStripeObject($stripeSubscription);
    }

    /** @throws ApiErrorException */
    public function resume(string $subscriptionId): StripeSubscription
    {
        $stripeSubscription = $this->stripe->subscriptions->update($subscriptionId, [
            "cancel_at_period_end" => false,
        ]);

        return StripeSubscription::fromStripeObject($stripeSubscription);
    }

    /**
     * @return Collection<int, StripeSubscription>
     *
     * @throws ApiErrorException
     */
    public function list(array $params = []): Collection
    {
        $stripeSubscriptions = $this->stripe->subscriptions->all($params);

        return collect($stripeSubscriptions->data)
            ->map(fn ($stripeSubscription): \EncoreDigitalGroup\Common\Stripe\Objects\Subscription\StripeSubscription => StripeSubscription::fromStripeObject($stripeSubscription));
    }

    /**
     * @return Collection<int, StripeSubscription>
     *
     * @throws ApiErrorException
     */
    public function search(string $query, array $params = []): Collection
    {
        $params["query"] = $query;
        $stripeSubscriptions = $this->stripe->subscriptions->search($params);

        return collect($stripeSubscriptions->data)
            ->map(fn ($stripeSubscription): \EncoreDigitalGroup\Common\Stripe\Objects\Subscription\StripeSubscription => StripeSubscription::fromStripeObject($stripeSubscription));
    }
}