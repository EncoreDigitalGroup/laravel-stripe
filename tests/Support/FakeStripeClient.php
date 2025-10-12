<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace Tests\Support;

use RuntimeException;
use Stripe\StripeClient;
use Stripe\StripeObject;
use Stripe\Util\Util;

/**
 * FakeStripeClient provides a fake implementation of StripeClient for testing.
 *
 * Similar to Laravel's Http::fake(), this allows you to fake Stripe API responses
 * without making actual HTTP requests.
 *
 * Example usage:
 *
 *     Stripe::fake([
 *         'customers.create' => ['id' => 'cus_123', 'email' => 'test@example.com'],
 *         'customers.retrieve' => ['id' => 'cus_123', 'email' => 'test@example.com'],
 *     ]);
 *
 *     // Now any Stripe API calls will use the faked responses
 *     $customer = Stripe::customers()->create(StripeCustomer::make());
 */
class FakeStripeClient extends StripeClient
{
    protected array $fakes = [];
    protected array $recorded = [];
    protected bool $shouldRecord = true;

    public function __construct(array $fakes = [], array $config = [])
    {
        // Convert enum keys to string values
        $this->fakes = $this->normalizeFakes($fakes);

        // Call parent constructor with fake API key
        $mergedConfig = array_merge(['api_key' => 'sk_test_fake'], $config);
        parent::__construct($mergedConfig);
    }

    /**
     * Normalize fakes array to convert enum keys to strings
     */
    protected function normalizeFakes(array $fakes): array
    {
        $normalized = [];
        foreach ($fakes as $key => $value) {
            $stringKey = $key instanceof \BackedEnum ? $key->value : $key;
            $normalized[$stringKey] = $value;
        }
        return $normalized;
    }

    /**
     * Register a fake response for a specific Stripe API call
     */
    public function fake(string|\BackedEnum $method, mixed $response): self
    {
        $stringMethod = $method instanceof \BackedEnum ? $method->value : $method;
        $this->fakes[$stringMethod] = $response;
        return $this;
    }

    /**
     * Register multiple fake responses at once
     */
    public function fakeMany(array $fakes): self
    {
        $this->fakes = array_merge($this->fakes, $this->normalizeFakes($fakes));
        return $this;
    }

    /**
     * Get all recorded API calls
     */
    public function recorded(): array
    {
        return $this->recorded;
    }

    /**
     * Check if a specific method was called
     */
    public function wasCalled(string $method): bool
    {
        return isset($this->recorded[$method]);
    }

    /**
     * Get the number of times a method was called
     */
    public function callCount(string $method): int
    {
        return count($this->recorded[$method] ?? []);
    }

    /**
     * Get the parameters passed to a specific method call
     */
    public function getCall(string $method, int $index = 0): ?array
    {
        return $this->recorded[$method][$index] ?? null;
    }

    /**
     * Clear all recorded calls
     */
    public function clearRecorded(): void
    {
        $this->recorded = [];
    }

    /**
     * Magic method to intercept Stripe API service access (e.g., $stripe->customers)
     */
    public function __get($name)
    {
        return new FakeStripeService($name, $this);
    }

    /**
     * Internal method to resolve a fake response
     */
    public function resolveFake(string $method, array $params = []): mixed
    {
        $this->recordCall($method, $params);

        // Check for exact match first
        if (isset($this->fakes[$method])) {
            return $this->processResponse($this->fakes[$method], $params);
        }

        // Check for wildcard matches
        $wildcardResponse = $this->findWildcardMatch($method);
        if ($wildcardResponse !== null) {
            return $this->processResponse($wildcardResponse, $params);
        }

        // No fake registered
        throw new RuntimeException(
            "No fake registered for Stripe method [{$method}]. " .
            "Register a fake using Stripe::fake(['{$method}' => ...]) in your test."
        );
    }

    /**
     * Record a method call with its parameters
     */
    protected function recordCall(string $method, array $params): void
    {
        if ($this->shouldRecord) {
            if (!isset($this->recorded[$method])) {
                $this->recorded[$method] = [];
            }
            $this->recorded[$method][] = $params;
        }
    }

    /**
     * Process a response (callable or array) into the appropriate format
     */
    protected function processResponse(mixed $response, array $params): mixed
    {
        // If it's a callable, execute it
        if (is_callable($response)) {
            $result = $response($params);
            // If callable returns array, convert to Stripe object
            if (is_array($result)) {
                return $this->arrayToStripeObject($result);
            }
            return $result;
        }

        // If it's an array, convert to Stripe object
        if (is_array($response)) {
            return $this->arrayToStripeObject($response);
        }

        return $response;
    }

    /**
     * Find a wildcard match for the given method
     */
    protected function findWildcardMatch(string $method): mixed
    {
        foreach ($this->fakes as $pattern => $response) {
            if (str_contains($pattern, '*')) {
                // Quote the pattern first, then replace escaped asterisks with .*
                $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/';
                if (preg_match($regex, $method)) {
                    return $response;
                }
            }
        }

        return null;
    }

    /**
     * Convert array to Stripe object-like structure
     */
    protected function arrayToStripeObject(array $data): StripeObject
    {
        // Ensure we have an 'object' key for proper conversion
        if (!isset($data['object']) && isset($data['id']) && is_string($data['id']) && $data['id'] !== '') {
            // Try to infer the object type from the ID prefix (before first underscore)
            $underscorePos = strpos($data['id'], '_');
            $prefix = $underscorePos !== false ? substr($data['id'], 0, $underscorePos) : $data['id'];

            $data['object'] = match ($prefix) {
                'cus' => 'customer',
                'sub' => 'subscription',
                'prod' => 'product',
                'price' => 'price',
                'ba' => 'bank_account',
                default => 'unknown'
            };
        }

        return Util::convertToStripeObject($data, []);
    }
}