<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Services;

use EncoreDigitalGroup\Stripe\Objects\Payment\StripePaymentIntent;
use EncoreDigitalGroup\Stripe\Support\Traits\HasStripe;
use Illuminate\Support\Collection;
use Stripe\Exception\ApiErrorException;

/** @internal */
class StripePaymentIntentService
{
    use HasStripe;

    /** @throws ApiErrorException */
    public function create(StripePaymentIntent $paymentIntent): StripePaymentIntent
    {
        $data = $paymentIntent->toArray();

        unset($data["id"], $data["created"], $data["client_secret"]);

        /** @phpstan-ignore-next-line */
        $stripePaymentIntent = $this->stripe->paymentIntents->create($data);

        return StripePaymentIntent::fromStripeObject($stripePaymentIntent);
    }

    /** @throws ApiErrorException */
    public function get(string $paymentIntentId): StripePaymentIntent
    {
        $stripePaymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

        return StripePaymentIntent::fromStripeObject($stripePaymentIntent);
    }

    /** @throws ApiErrorException */
    public function update(string $paymentIntentId, StripePaymentIntent $paymentIntent): StripePaymentIntent
    {
        $data = $paymentIntent->toArray();

        unset($data["id"], $data["created"], $data["client_secret"]);

        $stripePaymentIntent = $this->stripe->paymentIntents->update($paymentIntentId, $data);

        return StripePaymentIntent::fromStripeObject($stripePaymentIntent);
    }

    /** @throws ApiErrorException */
    public function confirm(string $paymentIntentId, array $params = []): StripePaymentIntent
    {
        $stripePaymentIntent = $this->stripe->paymentIntents->confirm($paymentIntentId, $params);

        return StripePaymentIntent::fromStripeObject($stripePaymentIntent);
    }

    /** @throws ApiErrorException */
    public function cancel(string $paymentIntentId, array $params = []): StripePaymentIntent
    {
        $stripePaymentIntent = $this->stripe->paymentIntents->cancel($paymentIntentId, $params);

        return StripePaymentIntent::fromStripeObject($stripePaymentIntent);
    }

    /** @throws ApiErrorException */
    public function capture(string $paymentIntentId, array $params = []): StripePaymentIntent
    {
        $stripePaymentIntent = $this->stripe->paymentIntents->capture($paymentIntentId, $params);

        return StripePaymentIntent::fromStripeObject($stripePaymentIntent);
    }

    /**
     * @return Collection<int, StripePaymentIntent>
     *
     * @throws ApiErrorException
     */
    public function list(array $params = []): Collection
    {
        $stripePaymentIntents = $this->stripe->paymentIntents->all($params);

        return collect($stripePaymentIntents->data)
            ->map(fn ($stripePaymentIntent): StripePaymentIntent => StripePaymentIntent::fromStripeObject($stripePaymentIntent));
    }

    /**
     * @return Collection<int, StripePaymentIntent>
     *
     * @throws ApiErrorException
     */
    public function search(string $query, array $params = []): Collection
    {
        $params["query"] = $query;
        $stripePaymentIntents = $this->stripe->paymentIntents->search($params);

        return collect($stripePaymentIntents->data)
            ->map(fn ($stripePaymentIntent): StripePaymentIntent => StripePaymentIntent::fromStripeObject($stripePaymentIntent));
    }
}
