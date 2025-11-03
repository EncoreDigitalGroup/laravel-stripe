<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Services;

use EncoreDigitalGroup\Stripe\Objects\Payment\StripeSetupIntent;
use EncoreDigitalGroup\Stripe\Support\Traits\HasStripe;
use Illuminate\Support\Collection;
use Stripe\Exception\ApiErrorException;
use Stripe\SetupIntent;

/** @internal */
class StripeSetupIntentService
{
    use HasStripe;

    /** @throws ApiErrorException */
    public function create(StripeSetupIntent $setupIntent): StripeSetupIntent
    {
        $data = $setupIntent->toArray();

        unset($data["id"], $data["created"], $data["client_secret"]);

        $stripeSetupIntent = $this->stripe->setupIntents->create($data);

        return StripeSetupIntent::fromStripeObject($stripeSetupIntent);
    }

    /** @throws ApiErrorException */
    public function get(string $setupIntentId): StripeSetupIntent
    {
        $stripeSetupIntent = $this->stripe->setupIntents->retrieve($setupIntentId);

        return StripeSetupIntent::fromStripeObject($stripeSetupIntent);
    }

    /** @throws ApiErrorException */
    public function update(string $setupIntentId, StripeSetupIntent $setupIntent): StripeSetupIntent
    {
        $data = $setupIntent->toArray();

        unset($data["id"], $data["created"], $data["client_secret"]);

        $stripeSetupIntent = $this->stripe->setupIntents->update($setupIntentId, $data);

        return StripeSetupIntent::fromStripeObject($stripeSetupIntent);
    }

    /** @throws ApiErrorException */
    public function confirm(string $setupIntentId, array $params = []): StripeSetupIntent
    {
        $stripeSetupIntent = $this->stripe->setupIntents->confirm($setupIntentId, $params);

        return StripeSetupIntent::fromStripeObject($stripeSetupIntent);
    }

    /** @throws ApiErrorException */
    public function cancel(string $setupIntentId, array $params = []): StripeSetupIntent
    {
        $stripeSetupIntent = $this->stripe->setupIntents->cancel($setupIntentId, $params);

        return StripeSetupIntent::fromStripeObject($stripeSetupIntent);
    }

    /**
     * @return Collection<int, StripeSetupIntent>
     *
     * @throws ApiErrorException
     */
    public function list(array $params = []): Collection
    {
        $stripeSetupIntents = $this->stripe->setupIntents->all($params);

        return collect($stripeSetupIntents->data)
            ->map(fn (SetupIntent $stripeSetupIntent): StripeSetupIntent => StripeSetupIntent::fromStripeObject($stripeSetupIntent));
    }
}