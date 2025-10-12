<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace Tests\Support;

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

    /**
     * Intercept method calls to the service (e.g., create(), retrieve(), update())
     */
    public function __call(string $method, array $arguments): mixed
    {
        $fullMethod = "{$this->serviceName}.{$method}";

        // Extract parameters - handle various argument patterns
        // Some methods take (id, params), others just (params)
        if (empty($arguments)) {
            $params = [];
        } elseif (count($arguments) === 1) {
            // Could be just ID (string) or params (array)
            $params = is_array($arguments[0]) ? $arguments[0] : ['id' => $arguments[0]];
        } else {
            // Multiple arguments - typically (id, params)
            // Check if second argument exists and is array
            $params = (isset($arguments[1]) && is_array($arguments[1])) ? $arguments[1] : [];
            if (isset($arguments[0])) {
                $params['id'] = $arguments[0];
            }
        }

        return $this->client->resolveFake($fullMethod, $params);
    }

    /**
     * Support for direct property access (e.g., $stripe->customers->all())
     */
    public function all(array $params = []): mixed
    {
        return $this->__call('all', [$params]);
    }

    public function create(array $params = []): mixed
    {
        return $this->__call('create', [$params]);
    }

    public function retrieve(string $id, array $params = []): mixed
    {
        return $this->__call('retrieve', array_merge([$id], $params));
    }

    public function update(string $id, array $params = []): mixed
    {
        return $this->__call('update', array_merge([$id], $params));
    }

    public function delete(string $id, array $params = []): mixed
    {
        return $this->__call('delete', array_merge([$id], $params));
    }

    public function search(array $params = []): mixed
    {
        return $this->__call('search', [$params]);
    }
}