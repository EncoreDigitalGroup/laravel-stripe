<?php

namespace EncoreDigitalGroup\Stripe\Services;

use EncoreDigitalGroup\Stripe\Objects\Webhook\StripeWebhookEndpoint;
use EncoreDigitalGroup\Stripe\Support\Traits\HasStripe;
use Illuminate\Support\Collection;
use Stripe\Exception\ApiErrorException;
use Stripe\WebhookEndpoint;

/** @internal */
class StripeWebhookEndpointService
{
    use HasStripe;

    /** @throws ApiErrorException */
    public function create(StripeWebhookEndpoint $endpoint): StripeWebhookEndpoint
    {
        $data = $endpoint->toArray();

        // Remove read-only fields that can't be sent on create
        unset(
            $data["id"],
            $data["created"],
            $data["livemode"],
            $data["secret"],
            $data["status"]
        );

        /** @phpstan-ignore argument.type */
        $stripeEndpoint = $this->stripe->webhookEndpoints->create($data);

        return StripeWebhookEndpoint::fromStripeObject($stripeEndpoint);
    }

    /** @throws ApiErrorException */
    public function get(string $endpointId): StripeWebhookEndpoint
    {
        $stripeEndpoint = $this->stripe->webhookEndpoints->retrieve($endpointId);

        return StripeWebhookEndpoint::fromStripeObject($stripeEndpoint);
    }

    /** @throws ApiErrorException */
    public function update(string $endpointId, StripeWebhookEndpoint $endpoint): StripeWebhookEndpoint
    {
        $data = $endpoint->toArray();

        // Remove read-only fields and id from update data
        unset(
            $data["id"],
            $data["created"],
            $data["livemode"],
            $data["secret"],
            $data["status"]
        );

        $stripeEndpoint = $this->stripe->webhookEndpoints->update($endpointId, $data);

        return StripeWebhookEndpoint::fromStripeObject($stripeEndpoint);
    }

    /** @throws ApiErrorException */
    public function delete(string $endpointId): StripeWebhookEndpoint
    {
        $stripeEndpoint = $this->stripe->webhookEndpoints->delete($endpointId);

        return StripeWebhookEndpoint::fromStripeObject($stripeEndpoint);
    }

    /**
     * @return Collection<int, StripeWebhookEndpoint>
     *
     * @throws ApiErrorException
     */
    public function list(array $params = []): Collection
    {
        $stripeEndpoints = $this->stripe->webhookEndpoints->all($params);

        return collect($stripeEndpoints->data)
            ->map(fn (WebhookEndpoint $stripeEndpoint): StripeWebhookEndpoint => StripeWebhookEndpoint::fromStripeObject($stripeEndpoint));
    }
}
