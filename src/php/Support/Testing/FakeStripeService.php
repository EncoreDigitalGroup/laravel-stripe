<?php

namespace EncoreDigitalGroup\Stripe\Support\Testing;

/**
 * FakeStripeService represents a faked Stripe service (e.g., customers, subscriptions)
 *
 * This class intercepts method calls to Stripe services and routes them to the
 * FakeStripeClient for response resolution.
 */
class FakeStripeService
{
    protected string $serviceName;
    protected FakeStripeClient $client;

    public function __construct(string $serviceName, FakeStripeClient $client)
    {
        $this->serviceName = $serviceName;
        $this->client = $client;
    }

    public function all(array $params = []): mixed
    {
        return $this->__call("all", [$params]);
    }

    public function create(array $params = []): mixed
    {
        return $this->__call("create", [$params]);
    }

    public function retrieve(string $id, array $params = []): mixed
    {
        return $this->__call("retrieve", [$id, $params]);
    }

    public function update(string $id, array $params = []): mixed
    {
        return $this->__call("update", [$id, $params]);
    }

    public function delete(string $id, array $params = []): mixed
    {
        return $this->__call("delete", [$id, $params]);
    }

    public function search(array $params = []): mixed
    {
        return $this->__call("search", [$params]);
    }

    public function __call(string $method, array $arguments): mixed
    {
        $fullMethod = "{$this->serviceName}.{$method}";

        // Extract parameters - handle various argument patterns
        // Some methods take (id, params), others just (params)
        if ($arguments === []) {
            $params = [];
        } elseif (count($arguments) === 1) {
            // Could be just ID (string) or params (array)
            $params = is_array($arguments[0]) ? $arguments[0] : [];
        } elseif (isset($arguments[1]) && is_array($arguments[1])) {
            // Multiple arguments - typically (id, params)
            // For methods like update, retrieve, delete, the ID is separate from params
            // We should not add it to the params array since real Stripe doesn't do that
            $params = $arguments[1];
        } else {
            $params = [];
        }

        return $this->client->resolveFake($fullMethod, $params);
    }
}