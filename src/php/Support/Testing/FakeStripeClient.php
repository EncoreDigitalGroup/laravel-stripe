<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Testing;

use BackedEnum;
use RuntimeException;
use Stripe\StripeClient;
use Stripe\StripeObject;
use Stripe\Util\Util;

/**
 * FakeStripeClient provides a fake implementation of StripeClient for testing.
 *
 * Similar to Laravel's Http::fake(), this allows you to fake Stripe API responses
 * without making actual HTTP requests.
 */
class FakeStripeClient extends StripeClient
{
    protected array $fakes = [];
    protected array $recorded = [];
    protected bool $shouldRecord = true;

    public function __construct(array $fakes = [], array $config = [])
    {
        $this->fakes = $this->normalizeFakes($fakes);

        $mergedConfig = array_merge(["api_key" => "sk_test_fake"], $config);
        parent::__construct($mergedConfig);
    }

    public function fake(string|BackedEnum $method, mixed $response): self
    {
        $stringMethod = $method instanceof BackedEnum ? $method->value : $method;

        $this->fakes[$stringMethod] = $response;

        return $this;
    }

    public function fakeMany(array $fakes): self
    {
        $this->fakes = array_merge($this->fakes, $this->normalizeFakes($fakes));

        return $this;
    }

    public function recorded(): array
    {
        return $this->recorded;
    }

    public function wasCalled(string $method): bool
    {
        return isset($this->recorded[$method]);
    }

    public function callCount(string $method): int
    {
        return count($this->recorded[$method] ?? []);
    }

    public function getCall(string $method, int $index = 0): ?array
    {
        return $this->recorded[$method][$index] ?? null;
    }

    public function clearRecorded(): void
    {
        $this->recorded = [];
    }

    /** @internal */
    public function resolveFake(string $method, array $params = []): mixed
    {
        $this->recordCall($method, $params);

        if (isset($this->fakes[$method])) {
            return $this->processResponse($this->fakes[$method], $params);
        }

        $wildcardResponse = $this->findWildcardMatch($method);
        if ($wildcardResponse !== null) {
            return $this->processResponse($wildcardResponse, $params);
        }

        throw new RuntimeException(
            "No fake registered for Stripe method [{$method}]. " .
            "Register a fake using Stripe::fake(['{$method}' => ...]) in your test."
        );
    }

    protected function normalizeFakes(array $fakes): array
    {
        $normalized = [];
        foreach ($fakes as $key => $value) {
            /** @phpstan-ignore-next-line */
            $stringKey = $key instanceof BackedEnum ? $key->value : (string) $key;

            $normalized[$stringKey] = $value;
        }

        return $normalized;
    }

    protected function recordCall(string $method, array $params): void
    {
        if ($this->shouldRecord) {
            if (!isset($this->recorded[$method])) {
                $this->recorded[$method] = [];
            }
            $this->recorded[$method][] = $params;
        }
    }

    protected function processResponse(mixed $response, array $params): mixed
    {
        if (is_callable($response)) {
            $result = $response($params);
            if (is_array($result)) {
                return $this->arrayToStripeObject($result);
            }

            return $result;
        }

        if (is_array($response)) {
            return $this->arrayToStripeObject($response);
        }

        return $response;
    }

    protected function findWildcardMatch(string $method): mixed
    {
        foreach ($this->fakes as $pattern => $response) {
            if (str_contains($pattern, "*")) {
                // Quote the pattern first, then replace escaped asterisks with .*
                $regex = "/^" . str_replace('\*', ".*", preg_quote($pattern, "/")) . '$/';
                if (preg_match($regex, $method)) {
                    return $response;
                }
            }
        }

        return null;
    }

    protected function arrayToStripeObject(array $data): StripeObject
    {
        // Ensure we have an 'object' key for proper conversion
        if (!isset($data["object"]) && isset($data["id"]) && is_string($data["id"]) && $data["id"] !== "") {
            // Try to infer the object type from the ID prefix (before first underscore)
            $underscorePos = strpos($data["id"], "_");

            $prefix = $underscorePos !== false ? substr($data["id"], 0, $underscorePos) : $data["id"];

            $data["object"] = match ($prefix) {
                "cus" => "customer",
                "sub" => "subscription",
                "prod" => "product",
                "price" => "price",
                "ba" => "bank_account",
                default => "unknown"
            };
        }

        $result = Util::convertToStripeObject($data, []);

        if (!$result instanceof StripeObject) {
            throw new RuntimeException("Failed to convert array to StripeObject");
        }

        return $result;
    }

    /**
     * @param  mixed  $name
     */
    public function __get($name): FakeStripeService
    {
        return new FakeStripeService((string) $name, $this);
    }
}